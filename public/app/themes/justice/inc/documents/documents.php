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
require_once 'permalinks.php';

/**
 * Actions and filters related to WordPress documents post type.
 */
class Documents
{

    // CPT slug. This is hardcoded in the plugin.
    const SLUG = 'document';
    // Hardcoded document slug. We don't want this to be changed by the user.
    const DOCUMENT_SLUG = 'documents';

    use DocumentColumns;
    use DocumentFilters;
    use DocumentPermalinks;

    // File extensions to mark as downloadable in S3.
    private array $content_disposition_extensions = [
        'doc',
        'docx',
        'pdf',
        'xls',
        'xlsx',
        'zip'
    ];

    private array $disallow_in_media_library = [
        'doc',
        'docx',
        'pdf',
        'xls',
        'xlsx',
        'zip'
    ];

    // Max filesize for wp-document-revisions to stream via php.
    private int|float $php_stream_limit = 15 * 1024 * 1024; // 15MB
    private int|float $default_upload_limit = 64 * 1024 * 1024; // 64MB
    private int|float $document_upload_limit = 256 * 1024 * 1024; // 256MB

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
        add_filter('document_slug', fn() => self::DOCUMENT_SLUG);
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

        add_filter('manage_' . self::SLUG . '_posts_columns', [$this, 'addColumns']);
        add_filter('manage_' . self::SLUG . '_posts_custom_column', [$this, 'addColumnContent'], null, 2);

        // Hide legacy redirects from users with the Editor capability
        add_action('pre_get_posts', [$this, 'redirectAdminFilter'], 10, 2);

        // Hide the Validate Structure sub-menu from non-admins.
        add_action('admin_menu', [$this, 'hideValidateStructureSubmenu'], 30);

        // Dequeue the frontend styles, we don't need them.
        add_action('wp_enqueue_scripts', fn() => wp_dequeue_style('wp-document-revisions-front'), 100);

        // Filter wp_die handler for documents - to change 403 to 404 for missing document files.
        add_filter('wp_die_handler', [self::class, 'filterWpDieHandler']);

        // Permalinks
        $this->addPermalinkHooks();
    }


    /**
     * Remove the editor for the document post type.
     *
     * @return void
     */

    public function hideEditor(): void
    {
        remove_post_type_support(self::SLUG, 'editor');
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

    public static function isDocument(int|WP_Post|null $post): bool
    {
        if ($post === null) {
            return false;
        }

        if (isset($post->post_type)) {
            return $post->post_type === self::SLUG;
        }

        return get_post_type($post) === self::SLUG;
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
            'Guidance on creating accessible permalinks can be found at ' .
            '<a href="https://howto-admin.www.justice.gov.uk/editing/documents.html#document-permalink" target="_blank">' .
            'Choosing a document permalink' .
            '</a>.</br>' .
            'Editing the permalink here will update the URL of the document, ' .
            'and frequent changes to the should be avoided. ' .
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

        $document = $this::getDocumentBySourcePath('/' . $wp->request);

        if (!isset($document?->ID)) {
            return;
        }

        wp_safe_redirect(get_permalink($document->ID), 301);
        exit;
    }


    /**
     * Get a document by the meta key _source_path.
     *
     * A helper function to lookup a document by the source path - a meta field set during migration.
     *
     * @param string $source_path - this should be the path of the old document, starting with a slash.
     * @return WP_Post|null
     */

    public static function getDocumentBySourcePath(string $source_path): ?WP_Post
    {
        $document = get_posts([
            'post_type' => self::SLUG,
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_source_path',
                    'value' => $source_path
                ]
            ],
        ]);

        return $document[0] ?? null;
    }


    /**
     * Get the document ID by the URL.
     *
     * This function is used to get the document ID by the URL.
     * It will first try to get the document by the source path - for legacy documents.
     * If that fails, it will try to get the document by the slug.
     *
     * @param ?string $url
     * @return int|null - Post ID, or 0 on failure.
     */
    public static function getDocumentIdByUrl(?string $url): Int
    {
        if (!$url || !is_string($url)) {
            return 0;
        }

        // Get the path from the URL.
        $path = parse_url($url, PHP_URL_PATH);

        if (!$path) {
            return 0;
        }

        // Get the document by the source path - for legacy documents.
        $document = self::getDocumentBySourcePath($path);

        if ($document?->ID) {
            return $document->ID;
        }

        $post_id = url_to_postid($path);

        return self::isDocument($post_id) ? $post_id : 0;
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
        add_meta_box('page', 'Document Attributes', [$this, 'metaBoxContent'], self::SLUG, 'side');
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
            if (str_starts_with($key, self::DOCUMENT_SLUG . '/')) {
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
        if (in_array($slug, [self::SLUG, self::DOCUMENT_SLUG])) {
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
        if (self::SLUG !== $post_type) {
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
        if (self::SLUG === $post_type) {
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
            self::SLUG => min($size, $this->document_upload_limit),
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

    /**
     * Returns the formatted filesize from any post_id to display in the file-download component
     *
     * @param int $post_id The ID of the attachment post
     * @return string|null The formatted filesize (to the largest byte unit)
     */
    public static function getFormattedFilesize(int $post_id): string|null
    {
        $filesize = null;

        // Init a WP_Document_Revisions class so that we can use document specific functions
        $document = new \WP_Document_Revisions;

        // If this is a document (from wp-document-revisions) get the attachment ID and set the $postId to that
        if (self::isDocument($post_id)) {
            $attachment = $document->get_document($post_id);
            if ($attachment?->ID ?? false) {
                // Update the $postId variable to the document's attachment ID
                $post_id = $attachment->ID;
                error_log('attachment ID: ' . $post_id);
            }
        }

        // Otherwise check the db for the saved filesize
        $post_meta = get_post_meta($post_id, '_wp_attachment_metadata', true);

        // Prefer the original filesize
        if (!empty($post_meta['filesize']) && is_int($post_meta['filesize'])) {
            $filesize = $post_meta['filesize'];
            // Or, get it it from the full size
        } else if (!empty($post_meta['sizes']['full']['filesize']) && is_int($post_meta['sizes']['full']['filesize'])) {
            $filesize = $post_meta['sizes']['full']['filesize'];
            // But if it's offloaded get the size saved by AS3CF
        } else {
            $offloaded_filesize = get_post_meta($post_id, 'as3cf_filesize_total', true);
            $filesize = !empty($offloaded_filesize) && is_int($offloaded_filesize) ? $offloaded_filesize : null;
        }
        return size_format($filesize);
    }


    /**
     * Filter the wp_die handler to use a custom wrapper for documents.
     *
     * The reason for the custom wrapper is that the WP Document Revisions plugin
     * calls wp_die with a 403 response code when a document file is missing.
     * We want to change the response code to 404, but there is no filter in the plugin to do this directly.
     * So we wrap the wp_die handler and modify the response code when necessary.
     *
     * @param callable $handler The original wp_die handler.
     * @return callable The filtered wp_die handler.
     */
    public static function filterWpDieHandler(callable $handler): callable
    {
        global $post;

        // If we are dealing with a document post type, and wpDieWrapper has not already been applied.
        if (is_object($post) && $post->post_type === 'document' && empty($post->wpdr_die_wrapper_applied)) {
            return [self::class, 'wpDieWrapper'];
        }

        // Otherwise, return the original handler.
        return $handler;
    }


    /**
     * Custom wp_die handler wrapper for documents.
     *
     * When a document is missing a file, we want to change the response code from 403 to 404.
     * This wrapper checks for that specific case and modifies the response code accordingly.
     *
     * This can be tested by creating a document, and not uploading a file to it.
     * Then publish the document and click the preview link, the resulting error should be a 404, not a 403.
     *
     * Note, to prevent an infinite loop, we set a property on the global $post object.
     * Therefore it is crucial to check that global $post is an object before calling this function.
     *
     * @param string|WP_Error $message The message to display.
     * @param string $title The title of the error.
     * @param string|array $args Additional arguments.
     * @return void
     */
    public static function wpDieWrapper($message, $title, string|array $args = []): void
    {
        global $post;

        // Create a `wpdr_die_wrapper_applied` property on the global $post object.
        // In `filterWpDieHandler`, this property is checked to avoid re-wrapping the wp_die handler.
        // This is essential to prevent an infinite loop.
        $post->wpdr_die_wrapper_applied = true;

        // There is a specific case where we want to change the response code from 403 to 404.
        // This is when the message is 'No document file is attached.' and the response code is 403.
        // See: `wp-document-revisions/includes/class-wp-document-revisions.php`
        $target_message = esc_html__('No document file is attached.', 'wp-document-revisions');
        if ($message === $target_message &&
            is_array($args) &&
            isset($args['response']) &&
            (int) $args['response'] === 403
        ) {
            $args['response'] = 404;
        }

        // Finally re-call wp_die function with the message and (possibly) modified args.
        wp_die($message, $title, $args);
    }
}
