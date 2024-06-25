<?php

namespace WPDR_RVY;

use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add compatibility with PublishPress Revisions, formerly known as Revisionary.
 * 
 * 
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
        if (!in_array('revisionary/revisionary.php', (array) get_option('active_plugins', array()))) {
            $this->log('Revisionary is not active');
            return;
        }

        // Remove the revision log meta box from the document revision edit screen.
        add_action('admin_head', [$this, 'removeRevisionLogMetaBox'], 10);

        // Remove UI elements that don't make sense when editing a revision.
        add_action('admin_head', [$this, 'revisionStyles'], 10);

        // Add a translation
        add_filter('gettext', [$this, 'updateRevisionText'], 10, 3);

        // Modify the permalink for revisions.
        add_filter('post_type_link', [$this, 'modifyPermalink'], 10, 4);

        // Hook into the document_serve_attachment filter.
        add_filter('document_serve_attachment', [$this, 'serveAttachment'], 10);
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
        if ($post === null) {
            return false;
        }

        if (isset($post->post_type)) {
            return $post->post_type === 'document';
        }

        return get_post_type($post) === 'document';
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
     * Remove the revision log meta box from the document edit screen, if we're editing a draft or pending revision.
     * 
     * This is because it does not show the revisions correctly and could be confusing to users.
     * 
     * @return void
     */

    public function removeRevisionLogMetaBox(): void
    {
        $screen = get_current_screen();

        if ($screen->post_type === 'document' && $screen->base === 'post' && $this->isDocumentRevision(get_the_ID())) {
            remove_meta_box('revision-log', 'document', 'normal');
        }
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
     * Update the text if we're editing a revision.
     * 
     * @param string $translation The translated text.
     * @param string $text The text to translate.
     * @param string $domain The text domain.
     * @return string The translated text.
     */

    public function updateRevisionText(string $translation, string $text, string $domain): string
    {
        if (!$this->isDocumentRevision(get_the_ID())) {
            return $translation;
        }

        if ($domain === 'wp-document-revisions' && $text === 'Latest Version of the Document') {
            return 'File for this Document Revision';
        }
        if ($domain === 'wp-document-revisions' && $text === 'Download') {
            return 'Preview';
        }

        return $translation;
    }

    /**
     * Modify the permalink for revisions.
     * 
     * This fixes the Preview buttons on the revision edit screen.
     * 
     * @param string $post_link The post link.
     * @param WP_Post $post The post object.
     * @return string The modified post link.
     */

    public  function modifyPermalink($post_link, $post): string
    {

        if (!$this->isDocumentRevision($post)) {
            return $post_link;
        }

        $this->log('will modifyPermalink');

        $post_link = str_replace(get_site_url(), get_home_url() . '/document', $post_link);

        return $post_link;
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
