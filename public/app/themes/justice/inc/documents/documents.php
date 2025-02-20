<?php

namespace MOJ\Justice;

use Exception;
use WP;
use WP_Post;
use WP_Query;
use const WP_CLI;

if (!defined('ABSPATH')) {
    exit;
}

require_once 'columns.php';
require_once 'filters.php';

/**
 * Actions and filters related to WordPress documents post type.
 */
class Documents
{

    use DocumentColumns;
    use DocumentFilters;

    // File extensions to mark as downloadable in S3.
    private array $content_disposition_extensions = [
        'doc', 'docx', 'pdf', 'xls', 'xlsx', 'zip'
    ];

    private array $disallow_in_media_library = [
        'doc', 'docx', 'pdf', 'xls', 'xlsx', 'zip'
    ];

    // Max filesize for wp-document-revisions to stream via php.
    private int|float $php_stream_limit = 15 * 1024 * 1024; // 15MB
    private int|float $default_upload_limit = 64 * 1024 * 1024; // 64MB
    private int|float $document_upload_limit = 256 * 1024 * 1024; // 256MB

    // CPT slug. This is hardcoded in the plugin.
    public string $slug = 'document';
    // Hardcoded document slug. We don't want this to be changed by the user.
    public string $document_slug = 'documents';

    // Is the wordpress-importer plugin running?
    private bool $is_importing = false;
    // PostMeta instance - set in the constructor.
    private PostMeta $post_meta;
    // Utils instance - set in the constructor.
    private Utils $utils;

    public function __construct()
    {
        $this->addHooks();
        $this->removeHooks();
        $this->post_meta = new PostMeta();
        $this->utils = new Utils();
    }


    /**
     * Add hooks to related to WordPress document post type.
     *
     * @return void
     */

    public function addHooks(): void
    {
        // Set default plugin options.
        add_filter('document_slug', fn() => $this->document_slug);
        add_filter('option_document_link_date', '__return_true');
        add_filter('default_option_document_link_date', '__return_true');
        // Importing.
        add_action('import_start', fn() => $this->is_importing = true);
        add_action('import_end', fn() => $this->is_importing = false);
        // Dashboard
        add_action('restrict_manage_posts', [$this, 'addFilteringDropdown']);
        add_filter('parse_query', [$this, 'editorFiltering']);
        add_action('admin_init', [$this, 'hideEditor']);
        add_action('edit_form_after_title', [$this, 'modifiedPrepareEditor']);
        // Serving documents
        add_filter('document_serve_use_gzip', [$this, 'filterGzip'], null, 2);
        add_filter('document_serve', [$this, 'maybeRedirectToAttachmentUrl'], null, 3);
        add_action('template_redirect', [$this, 'redirectLegacyDocumentUrls']);
        // S3
        add_filter('as3cf_object_meta', [$this, 'addObjectMeta'], 10, 4);
        // Add parent page to the document post type.
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_filter('document_permalink', [$this, 'addParentPagesToPermalink'], 20, 2);
        add_filter('document_rewrite_rules', [$this, 'addRewriteRules'], null, 2);
        // Prevent posts using document(s) slug. * Affects documents & non-documents.
        add_filter('wp_unique_post_slug_is_bad_hierarchical_slug', [$this, 'isInvalidSlug'], 10, 2);
        add_filter('wp_unique_post_slug_is_bad_flat_slug', [$this, 'isInvalidSlug'], 10, 2);
        // Media Library hint regarding unsupported file types. * Affects non-documents.
        add_filter('post-upload-ui', [$this, 'mediaLibraryHint'], 10);
        // Remove support for document file types from the Media Library. * Affects non-documents.
        add_filter('upload_mimes', [$this, 'removeFileSupport'], 10);
        // Limits on uploads. * Affects documents & non-documents.
        add_filter('upload_size_limit', [$this, 'setUploadSizeLimit'], 10, 3);

        add_filter('manage_' . $this->slug . '_posts_columns', [$this, 'addColumns']);
        add_filter('manage_' . $this->slug . '_posts_custom_column', [$this, 'addColumnContent'], null, 2);

        // Hide legacy redirects from users with the Editor capability
        add_action('pre_get_posts', [$this, 'redirectAdminFilter'], 10, 2);

        // Hide the Validate Structure sub-menu from non-admins.
        add_action('admin_menu', [$this, 'hideValidateStructureSubmenu'], 30);

        // Dequeue the frontend styles, we don't need them.
        add_action('wp_enqueue_scripts', fn() => wp_dequeue_style('wp-document-revisions-front'), 100);
    }


    /**
     * Remove the editor for the document post type.
     *
     * @return void
     */

    public function hideEditor(): void
    {
        remove_post_type_support($this->slug, 'editor');
    }

    /**
     * Remove the title: Document Description
     *
     * @return void
     * @global $wpdr
     *
     */

    public function removeHooks(): void
    {
        global $wpdr;
        if (!isset($wpdr)) {
            return;
        }
        remove_action('edit_form_after_title', [$wpdr->admin, 'prepare_editor']);
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
            return $post->post_type === $this->slug;
        }

        return get_post_type($post) === $this->slug;
    }

    /**
     * Check if the attachment is attached to a document post.
     *
     * @param int $attach_id
     * @return bool
     */

    public function isDocumentAttachment(int $attach_id): bool
    {
        return $this->isDocument(wp_get_post_parent_id($attach_id));
    }

    /**
     * Echo a hint to the editor to explain the slug.
     *
     * @param WP_Post $post
     * @return void
     */

    public function modifiedPrepareEditor(WP_Post $post): void
    {
        if (!$this->isDocument($post)) {
            return;
        }

        echo '<p>' .
            'Editing the permalink here will update the URL of the document. ' .
            'Only do this before sharing/publishing new documents. ' .
            'Editing the permalink for an established document will result in a broken link.' .
            '</p>';
    }

    /**
     * Should the file be gzipped? Don't gzip zip files.
     *
     * @param bool $gzip
     * @param string $mimetype
     * @return bool
     */

    public function filterGzip(bool $gzip, string $mimetype): bool
    {

        if ('application/zip' === $mimetype) {
            return false;
        }

        return $gzip;
    }

    /**
     * Stream the file via php, or redirect the user to the attachment URL (could be S3, CDN etc.).
     *
     * This function will either:
     * - Stream the file via php, if the filesize is below the limit.
     * - Redirect the user to the attachment URL, if the filesize is above the limit.
     *
     * @param string $file
     * @param int $post_id
     * @param int $attach_id
     * @return string
     */

    public function maybeRedirectToAttachmentUrl(string $file, int $post_id, int $attach_id): string
    {

        // Only redirect published files to the CDN.
        if (get_post_status($post_id) !== 'publish') {
            return $file;
        }

        // Get the filesize.
        $file_size = filesize(get_attached_file($attach_id));

        // If it's too big then redirect to the CDN.
        if ($file_size > $this->php_stream_limit) {
            // Be aware that this url will still work even if the document visibility is later changed to private.
            // This should not be a problem with justice.gov.uk as the document visibility is always public.
            // It is becoming an issue, let's sign the URLs with a short expiry time.
            $url = wp_get_attachment_url($attach_id);
            wp_redirect($url);
            exit;
        }

        return $file;
    }

    /**
     * Redirect to the new document URL if the request matches a _source_path.
     *
     * During migration, it was not possible to have an exact match between old and new document URLs.
     * The path of the old document is stored in the source_path meta field.
     * If we have a 404 and the request matches a _source_path then redirect to the new document URL.
     *
     * @return void
     * @global WP $wp
     *
     */

    public function redirectLegacyDocumentUrls(): void
    {
        if (!is_404()) {
            return;
        }

        global $wp;
        $ext = pathinfo($wp->request, PATHINFO_EXTENSION);

        if (!$ext || $ext === 'php') {
            return;
        }

        $document = get_posts([
            'post_type' => $this->slug,
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_source_path',
                    'value' => '/' . $wp->request
                ]
            ],
        ]);

        if (!isset($document[0]?->ID)) {
            return;
        }

        wp_safe_redirect(get_permalink($document[0]->ID), 301);
        exit;
    }

    /**
     * Add object meta to the S3 object.
     *
     * This function is called whenever any file is uploaded to S3.
     * Via the Media Library or the Document post type.
     *
     * @param array $args
     * @param int $attach_id
     * @return array
     */

    public function addObjectMeta(array $args, int $attach_id): array
    {

        // Return if we're not dealing with a document attachment.
        if (!$this->isDocumentAttachment($attach_id)) {
            return $args;
        }

        // Get info based on the attachment URL.
        $path_info = pathinfo($args['Key']);

        // Return if we don't need to mark the url as a download, based on file extension.
        if (!in_array($path_info['extension'], $this->content_disposition_extensions)) {
            return $args;
        }

        // Mark as downloadable download.
        $args['ContentDisposition'] = 'attachment';

        // If filename is hex & 32 chars long, then set a ContentDisposition filename.
        if (preg_match('/^[a-f0-9]{32}$/', $path_info['filename'])) {
            // Get the basename from the request.
            $content_disposition_basename = sanitize_file_name($_REQUEST['name']);

            // Get the document ID, permalink and filename.
            $document_id = wp_get_post_parent_id($attach_id);
            $document_permalink = get_permalink($document_id);

            // If the permalink is set then use that as the filename.
            if (!str_contains($document_permalink, '?post_type=document&p=')) {
                $document_filename = pathinfo($document_permalink, PATHINFO_FILENAME);
                // Build a basename from the document permalink filename with the file extension.
                $content_disposition_basename = $document_filename . '.' . $path_info['extension'];
            }

            // Write a filename to S3 metadata. This is what the downloaded file will be called.
            $args['ContentDisposition'] .= ';filename="' . $content_disposition_basename . '"';
        }

        return $args;
    }

    /*
     * 4 functions related to adding a parent page to the document post type.
     * - addMetaBoxes
     * - metaBoxContent
     * - addParentPagesToPermalink
     * - addRewriteRules
     */

    /**
     * Add a parent page meta box to the document post type.
     *
     * @return void
     */

    public function addMetaBoxes(): void
    {
        add_meta_box('page', 'Document Attributes', [$this, 'metaBoxContent'], $this->slug, 'side');
    }

    /**
     * Echo the dropdown of parent pages.
     *
     * @param WP_Post $post
     * @throws Exception
     */

    public function metaBoxContent(WP_Post $post): void
    {
        $pages = wp_dropdown_pages(
            array(
                'post_type' => 'page',
                'selected' => $post->post_parent,
                'name' => 'parent_id',
                'show_option_none' => __('(no parent)'),
                'sort_column' => 'menu_order, post_title',
                'echo' => 0
            )
        );

        $verb = $post->post_parent ? 'Changing' : 'Setting';

        if (!empty($pages)) {
            echo '<label class="screen-reader-text" for="parent_id">Parent page</label>';
            echo '<p>Parent page</p>';
            echo $pages;
            echo '<p>' . $verb . ' the parent page will update the permalink of the document. ' .
                'This part of the URL is presentational, and will not result in broken links.</p>';
        }
    }

    /**
     * Modifies the permalink of a document to include the parent page.
     *
     * e.g. /documents/document-name.ext -> /grand-parent-page/parent-page/documents/document-name.ext
     *
     * @param string $link
     * @param WP_Post $document
     * @return string
     */

    public function addParentPagesToPermalink(string $link, WP_Post $document): string
    {
        if ($document->post_parent) {
            $link = str_replace(home_url(), get_permalink($document->post_parent), $link);
        }

        return $link;
    }

    /**
     * Add rewrite rules to accommodate the parent page in the document permalink.
     *
     * Means that example.com/documents/my-file.doc will work as usual.
     * Also means that example.com/grand-parent-page/parent-page/documents/my-file.doc will work.
     * grand-parent-page/parent-page/documents/ is matched by the regex pattern: `.*\/documents/`
     *
     * @param array $rules
     * @return array
     */

    public function addRewriteRules(array $rules): array
    {

        $new_rules = [];

        foreach ($rules as $key => $value) {
            // If starts with documents/ then prefix with a  pattern to match any page slug.
            if (str_starts_with($key, $this->document_slug . '/')) {
                $new_key = '.*\/' . $key;
                $new_rules[$new_key] = $value;
            }
        }

        return array_merge($new_rules, $rules);
    }

    /*
     * Functions related to documents, also having an effect on non-documents.
     * - isInvalidSlug
     * - mediaLibraryHint
     * - removeFileSupport
     * - setUploadSizeLimit
     */


    /**
     * Prevent all single posts, of any post type, from using the document(s) slug.
     *
     * This prevents a conflict, where nested pages may make a URL like /documents/my-document
     *
     * @param bool $is_invalid_slug
     * @param string $slug Should always be a string, but a default is set just in-case.
     * @return bool
     */

    public function isInvalidSlug(bool $is_invalid_slug, string $slug = ''): bool
    {
        if (in_array($slug, [$this->slug, $this->document_slug])) {
            return true;
        }
        return $is_invalid_slug;
    }

    /**
     * Echo hint to help users to understand that documents should not be loaded to the Media Library.
     *
     * @return void
     */

    public function mediaLibraryHint(): void
    {
        $post_type = isset($_REQUEST['post_id']) ? get_post_type($_REQUEST['post_id']) : null;

        // We're not uploading a document.
        if ($this->slug !== $post_type) {
            echo sprintf(
                '<p>Are you uploading file types: %1$s etc. ? Try to <a href="%2$s">add document</a> instead.</p>',
                join(', ', $this->disallow_in_media_library),
                admin_url('post-new.php?post_type=document')
            );
        }
    }

    /**
     * Remove support for document file types from the Media Library.
     *
     * @param array $mime_types
     * @return array
     */

    public function removeFileSupport(array $mime_types): array
    {

        // We're using the WP CLI or running the wordpress-import plugin.
        if ((defined('WP_CLI') && WP_CLI) || $this->is_importing) {
            return $mime_types;
        }

        $post_type = isset($_REQUEST['post_id']) ? get_post_type($_REQUEST['post_id']) : null;

        // We're uploading a document.
        if ($this->slug === $post_type) {
            return $mime_types;
        }

        // We're uploading via the Media Library or non-document.
        // Remove the disallowed file types.
        foreach ($this->disallow_in_media_library as $ext) {
            unset($mime_types[$ext]);
        }

        return $mime_types;
    }

    /**
     * Set an upload size limit based on the post type.
     *
     * As we're setting a very high limit for uploads at the server level,
     * we need to limit the upload size for the media library at the application level.
     *
     * @param int $size
     * @return int
     */

    public function setUploadSizeLimit(int $size): int
    {
        $post_type = isset($_REQUEST['post_id']) ? get_post_type($_REQUEST['post_id']) : null;

        return match ($post_type) {
            $this->slug => min($size, $this->document_upload_limit),
            default => min($size, $this->default_upload_limit)
        };
    }

    /**
     * Hide the redirects created by administrators for editors
     *
     * Having 5000+ legacy redirects might be confusing for editors so we'll filter any
     * that were created by administrators out of the redirect manager list
     *
     * @param WP_Query $query
     *
     * @return WP_Query
     */
    public function redirectAdminFilter(WP_Query $query): WP_Query
    {
        if ($query->get('post_type') === 'redirect_rule') {
            if (!current_user_can('administrator')) {
                $user_ids = get_users([
                    'role'   => 'administrator',
                    'fields' => 'ID'
                ]);
                $query->set('author__not_in', $user_ids);
            }
        }
        return $query;
    }

    /**
     * Hide the Validate Structure sub-menu from non-admins.
     *
     * @return void
     */

    public function hideValidateStructureSubmenu()
    {
        if (!current_user_can('administrator')) {
            remove_submenu_page('edit.php?post_type=document', 'wpdr_validate');
        }
    }
}
