<?php

namespace WPDR_RVY;

use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add compatibility with PublishPress Revisions, formerly known as Revisionary.
 * 
 * @see https://wp-document-revisions.github.io/wp-document-revisions/
 * @see https://github.com/wp-document-revisions/wp-document-revisions
 * @see https://publishpress.com/revisions/
 * @see https://github.com/publishpress/PublishPress-Revisions
 */

class WP_Document_Revisions_Compatibility
{

    private $debug = false;

    public function __construct()
    {
        $this->debug = defined('WP_DEBUG') && WP_DEBUG ?? false;

        $this->addHooks();
    }

    public function addHooks(): void
    {
        if (!$this->is_plugin_active('revisionary/revisionary.php')) {
            $this->log('PublishPress Revisions (Revisionary) is not active');
            return;
        }

        if (!$this->is_plugin_active('wp-document-revisions/wp-document-revisions.php')) {
            $this->log('WP Document Revisions is not active');
            return;
        }

        // Reorder the admin menu - Documents above Revisions.
        add_action('admin_menu', [$this, 'reorderAdminMenu'], 20);

        // Remove the revision log meta box from the document revision edit screen, swap it on document edit screen.
        add_action('document_edit', [$this, 'removeAndSwapRevisionLogMetaBox'], 10);

        // Remove UI elements that don't make sense when editing a revision.
        add_action('admin_head', [$this, 'revisionStyles'], 10);

        // Add text replacements
        add_filter('gettext', [$this, 'updateRevisionText'], 10, 3);

        // Maybe redirect requests for revisions previews.
        add_action('template_redirect', [$this, 'revisionRedirect'], 15);

        // Hook into the document_serve_attachment filter.
        add_filter('document_serve_attachment', [$this, 'serveAttachment'], 10);
    }

    /**
     * Check if a plugin is active. Works when WordPress's is_plugin_active has not been loaded.
     * 
     * @param string $plugin The plugin file name.
     * @return bool
     */

    public function is_plugin_active(string $plugin): bool
    {
        return in_array($plugin, (array) get_option('active_plugins', array()));
    }

    /**
     * Log to the error log.
     * 
     * @param string $message The message to log.
     * @param mixed $data optional Any data to log.
     * @return void
     */

    public function log(string $message, $data = null): void
    {
        if (!$this->debug) {
            return;
        }

        error_log('WPDR_RVY: ' . $message . ' ' . print_r($data, true));
    }

    /**
     * Is a post of CPT document?
     *
     * @param int|WP_Post|null $post
     * @return bool
     */

    public function isDocument(int|WP_Post|null $post): bool
    {
        global $wpdr;

        return $wpdr->verify_post_type($post);
    }

    /**
     * Is the post a revision?
     * 
     * @param int|WP_Post|null $post
     * @return bool
     */

    public function isDocumentRevision(int|WP_Post|null $post): bool
    {
        if ($post === null) {
            return false;
        }

        if (!$this->isDocument($post)) {
            return false;
        }

        if (isset($post->post_mime_type)) {
            return str_contains($post->post_mime_type, 'revision');
        }

        return str_contains(get_post_mime_type($post), 'revision');
    }

    /**
     * Move the Revisions menu item below the Documents.
     * 
     * @return void
     */

    public function reorderAdminMenu(): void
    {
        $this->log('in reorderAdminMenu');

        global $menu;

        $revisionary_key = null;
        $document_key = null;

        // Loop over menu to get the keys for the revisionary-q and document menu items.
        foreach ($menu as $key => $value) {
            if ($value[2] === 'revisionary-q') {
                $revisionary_key = $key;
            }
            if ($value[2] === 'edit.php?post_type=document') {
                $document_key = $key;
            }
            if ($revisionary_key && $document_key) {
                break;
            }
        }


        if ($revisionary_key && $document_key &&  floatval($revisionary_key) < floatval($document_key)) {

            $new_revisionary_key = $document_key + 1;

            // Find a new key that doesn't exist.
            while (isset($menu[$new_revisionary_key])) {
                $new_revisionary_key++;
            }

            $this->log('moving revisionary-q menu item');
            $menu[$new_revisionary_key] = $menu[$revisionary_key];
            unset($menu[$revisionary_key]);
        }
    }

    /**
     * Remove the revision log meta box from the document edit screen.
     * 
     * If we're editing a draft or pending revision it does not make sense to show the Revision Log meta box.
     * If we're editing a published revision, the Revision Log meta box is replaced with a custom one.
     * 
     * @return void
     */

    public function removeAndSwapRevisionLogMetaBox(): void
    {
        remove_meta_box('revision-log', 'document', 'normal');

        $this->log('in addRevisionLogMetaBox');

        if (!$this->isDocumentRevision(get_the_ID())) {
            add_meta_box(
                'revision-log',
                __('Revision Log', 'wp-document-revisions'),
                [$this, 'revisionMetabox'],
                'document',
                'normal',
                'low'
            );
        }
    }

    /**
     * Custom Revision Log metabox.
     * 
     * This metabox is added to the document edit screen when editing a document (not a document revision).
     * 
     * @param WP_Post $post The post object.
     * @return void
     */

    public function revisionMetabox($post)
    {

        global $wpdr;

        if (!isset($wpdr) || !function_exists('rvy_get_post_revisions')) {
            return;
        }

        $revisionary_revisions = rvy_get_post_revisions($post->ID);

        echo '<p>' . _e('The table shows the <strong>published</strong> revisions for this document.', 'wp-document-revisions')  . ' </p>';

        if ($revisionary_revisions && sizeof($revisionary_revisions)) {
            echo '<p>';
            _e('There are also <strong>non-published</strong> revision(s) in the ', 'wp-document-revisions');
            echo sprintf('<a href="%s">%s</a>', admin_url('/admin.php?page=revisionary-q'), esc_html('Revision Queue', 'revisionary'));
            echo '.</p>';
        }

        $wpdr->admin->revision_metabox($post);
    }

    /**
     * Remove UI elements that don't make sense when editing a revision.
     * 
     * Remove
     * - Compare button
     */

    public function revisionStyles(): void
    {
        if ($this->isDocumentRevision(get_the_ID())) {
            echo "\n<style type='text/css'>\n<!--\n";

            echo "#rvy_compare_button { display: none !important; }\n";

            echo "-->\n</style>\n";
        }
    }

    /**
     * Add to the Revision Log meta box.
     * 
     * Add revisions from the Revision Queue to the Revision Log meta box.
     * 
     */

    /**
     * Update the text if we're editing a revision.
     * 
     * @param string $translation The translated text.
     * @param string $text The text to translate.
     * @param string $domain The text domain.
     * @return string The translated text.
     */

    public function updateRevisionText(string $translation, string $text, string $domain): string
    {

        $id = get_the_ID();
        $is_document = $this->isDocument($id);
        $is_revision = $this->isDocumentRevision($id);

        if ($is_document && $domain === 'revisionary' && $text === 'Has Revision') {
            return 'Has Revision in Queue';
        }

        if ($is_revision && $domain === 'wp-document-revisions' && $text === 'Latest Version of the Document') {
            return 'File for this Document Revision';
        }

        if ($is_revision && $domain === 'wp-document-revisions' && $text === 'Download') {
            return 'Preview';
        }

        return $translation;
    }

    /**
     * Redirect document revision preview links - only on sites where home_url is different from site_url.
     * 
     * This fixes the Preview button and the Download button 
     * (that has been renamed to Preview) on the revision edit screen.
     * e.g. https://mysite.com/wp/?post_type=document&p=123 -> https://mysite.com/?post_type=document&p=25397
     * 
     * @return void
     */

    public function revisionRedirect(): void
    {
        // If we're not on a 404 page, or we're not viewing a document revision, return.
        if (!is_404() || !isset($_GET['p']) || !$this->isDocumentRevision($_GET['p'])) {
            return;
        }

        // If the home_url is the same as the site_url, we don't need to redirect.
        if (get_site_url() === get_home_url()) {
            return;
        }

        // Build up the current url from get_site_url and $_SERVER.
        $current_url = parse_url(get_site_url(), PHP_URL_SCHEME) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        // Build a new url based on the current url.
        $new_url = str_replace(get_site_url(), get_home_url(), $current_url);

        // If the new url is different from the current url, redirect.
        if ($new_url !== $current_url) {
            $this->log('Redirecting to ', $new_url);
            wp_redirect($new_url);
            exit;
        }
    }

    /**
     * Hook into the document_serve_attachment filter. Update the attachment's post_parent if necessary.
     * 
     * When PublishPress Revisions is used to schedule, and subsequently, publish a revision, 
     * the resulting data in the database does not match the expected wp-document-revisions structure.
     * 
     * It is expected that when a revision is published that the attachment's post_parent is the document post ID.
     * What actually happens is that the attachment's post_parent is the revision post ID.
     * 
     * This filter checks if the attachment's post_parent is a revision post ID and updates it to the document post ID.
     * 
     * To test this functionality, 
     * - create a document and save it.
     * - create a revision of the document and upload a new version.
     * - click update revision.
     * - set publish on approval.
     * - submit the revision for approval.
     * - refresh the edit page.
     * - click Approve revision.
     * - the new version of the document is now published.
     * - when it is viewed for the first time, the attachment's post_parent is updated to the document post ID.
     *   if you have WP_DEBUG enabled, you will see a log message in the error log.
     *   e.g. wpdr_rvy: Attachment: 25352 has parent: 25351 which is a revision. Updating parent to: 25331 
     * 
     * @param WP_Post $attach The attachment post object. 
     * @return WP_Post The attachment post object.
     */

    public function serveAttachment($attach)
    {
        $this->log('In serveAttachment');

        // Get the global wpdb object.
        global $wpdb;
        $post_table = "{$wpdb->prefix}posts";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $parent = $wpdb->get_var($wpdb->prepare("SELECT post_parent FROM `$post_table` WHERE ID = %d ", $attach->post_parent));

        // Is the parent of the attachment a document (correct) or a revision (approved revision)?
        if (0 !== (int) $parent) {

            // The parent of the attachment is a revision.
            $this->log('Attachment: ' . $attach->ID . ' has parent: ' . $attach->post_parent .  ' which is a revision. Updating parent to: ' . $parent);

            // Update attachment post in the database.
            wp_update_post(array(
                'ID'          => $attach->ID,
                'post_parent' => $parent,
            ));

            // Update the attachment post object.
            $attach->post_parent = $parent;
        }

        // Return the attachment post object.
        return $attach;
    }
}
