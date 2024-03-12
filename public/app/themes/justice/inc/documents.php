<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Documents
 * Actions and filters related to WordPress documents post type.
 */

class Documents
{

    // File extensions to mark as downloadable in S3.
    private $content_disposition_extensions = [
        'doc', 'docx', 'pdf', 'xls', 'xlsx', 'zip'
    ];

    // Max filesize for wp-document-revisions to stream via php.
    private $php_stream_limit = 15 * 1024 * 1024; // 15MB
    private $default_upload_limit = 64 * 1024 * 1024; // 64MB
    private $document_upload_limit = 200 * 1024 * 1024; // 200MB

    // CPT slug. This is hardcoded in the plugin.
    public $slug = 'document';
    // Hardcoded document slug. We don't want this to be changed by the user.
    public $document_slug = 'documents';

    public function __construct()
    {
        $this->addHooks();
        $this->removeHooks();
    }

    public function addHooks()
    {
        // Set default plugin options.
        add_filter('document_slug', fn () => $this->document_slug);
        add_filter('option_document_link_date', '__return_true');
        // Dashboard
        add_action('admin_init', [$this, 'hideEditor']);
        // Serving documents
        add_filter('document_serve_use_gzip', [$this, 'filterGzip'], null, 3);
        add_filter('document_serve', [$this, 'maybeRedirectToAttachmentUrl'], null, 3);
        // S3
        add_filter('as3cf_object_meta', [$this,  'addObjectMeta'], 10, 4);
        // Limits on uploads.
        add_filter('upload_size_limit', [$this,  'setUploadSizeLimit'], 10, 3);
        // Add parent page to the document post type.
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_filter('document_permalink', [$this,  'addParentPagesToPermalink'], 20, 2);
        add_filter('document_rewrite_rules', [$this,  'addRewriteRules'], null, 2);
    }

    /**
     * Remove the editor for the document post type.
     */

    public function hideEditor()
    {
        remove_post_type_support($this->slug, 'editor');
    }

    /**
     * Remove the title: Document Description
     */

    public function removeHooks()
    {
        global $wpdr;
        if (!isset($wpdr)) {
            return;
        }
        remove_action('edit_form_after_title', [$wpdr->admin, 'prepare_editor']);
    }

    /**
     * isDocument
     */

    public function isDocument(int|\WP_Post|null $post): bool
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
     * isDocumentAttachment
     * Check if the attachment is attached to a document post.
     */

    public function isDocumentAttachment($attach_id): bool
    {
        return $this->isDocument(wp_get_post_parent_id($attach_id));
    }

    /**
     * filterGzip
     * Should the file be gzipped? Don't gzip zip files.
     */

    public function filterGzip($gzip, $mimetype, $filesize)
    {

        if ('application/zip' === $mimetype) {
            return false;
        }

        return $gzip;
    }

    /**
     * maybeRedirectToAttachmentUrl
     * Stream the file via php, or redirect the user to the attachment URL (could be S3, CDN etc.).
     */

    public function maybeRedirectToAttachmentUrl($file, $post_id, $attach_id)
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
            // It it becomes an issue, let's sign the URLs with a short expiry time.
            $url = wp_get_attachment_url($attach_id);
            wp_redirect($url);
            exit;
        }

        return $file;
    }

    /**
     * addObjectMeta
     * Add object meta to the S3 object.
     * This function is called whenever any file is upladed to S3.
     * Via the Media Library or the Document post type.
     */

    public function addObjectMeta($args, $attach_id)
    {

        // Return if we're not dealing with a document attachment.
        if (!$this->isDocumentAttachment($attach_id)) {
            return $args;
        }

        // Get info based on the attachment URL.
        $pathinfo = pathinfo($args['Key']);

        // Return if we don't need to mark the url as a download, based on file extension.
        if (!in_array($pathinfo['extension'], $this->content_disposition_extensions)) {
            return $args;
        }

        // Mark as downloadable download.
        $args['ContentDisposition'] = 'attachment';

        // If filename is hex & 32 chars long, then set a ContentDisposition filename.
        if (preg_match('/^[a-f0-9]{32}$/', $pathinfo['filename'])) {
            // Get the basename from the request.
            $content_disposition_basename = sanitize_file_name($_REQUEST['name']);

            // Get the document ID, permalink and filename.
            $document_id = wp_get_post_parent_id($attach_id);
            $document_permalink = get_permalink($document_id);

            // If the permalink is set then use that as the filename.
            if (!str_contains($document_permalink, '?post_type=document&p=')) {
                $document_filename = pathinfo($document_permalink, PATHINFO_FILENAME);
                // Build a basename from the document permalink filename with the file extension.
                $content_disposition_basename = $document_filename . '.' . $pathinfo['extension'];
            }

            // Write a filename to S3 metadata. This is what the downloaded file will be called.
            $args['ContentDisposition'] .= ';filename="' . $content_disposition_basename . '"';
        }

        return $args;
    }

    /**
     * limitUploadSize
     * As we're setting a very high limit for uploads at the server level,
     * we need to limit the upload size for the media library at the application level.
     */

    public function setUploadSizeLimit(int $size): int
    {

        $post_type = isset($_REQUEST['post_id']) && get_post_type($_REQUEST['post_id']);

        switch ($post_type) {
            case 'document':
                return min($size, $this->document_upload_limit);
            default:
                return min($size, $this->default_upload_limit);
        }
    }


    /**
     * 4 functions related to adding a parent page to the document post type.
     * - addMetaBoxes
     * - metaBoxContent
     * - addParentPagesToPermalink
     * - addRewriteRules
     */

    /**
     * addMetaBoxes
     * Add a parent page meta box to the document post type.
     */

    public function addMetaBoxes()
    {
        add_meta_box('page', 'Post Attributes', [$this, 'metaBoxContent'], $this->slug, 'side', 'default');
    }

    /**
     * metaBoxContent
     * Echo the dropdown of parent pages.
     */

    public function metaBoxContent($post)
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

        if (!empty($pages)) {
            echo $pages;
        }
    }

    /**
     * addParentPagesToPermalink
     * Modifies the permalink of a document to include the parent page.
     * e.g. /grand-parent-page/parent-page/document-name.ext
     */

    public function addParentPagesToPermalink($link, $document)
    {
        if ($document->post_parent) {
            $link = str_replace(home_url(), get_permalink($document->post_parent), $link);
        }

        return $link;
    }

    /**
     * addRewriteRules
     * Add rewrite rules to accommodate the parent page in the document permalink.
     * Means that example.com/documents/my-file.doc will work as usual.
     * Also means that example.com/grand-parent-page/parent-page/documents/my-file.doc will work.
     * grand-parent-page/parent-page/documents/ is matched by the regex pattern: `.*\/documents/`
     */

    public function addRewriteRules($rules)
    {

        $new_rules = [];

        foreach ($rules as $key => $value) {
            // If starts with documents/ then prefix with a  pattern to match any page slug.
            if (strpos($key, $this->document_slug . '/') === 0) {
                $new_key = '.*\/' . $key;
                $new_rules[$new_key] = $value;
            }
        }

        return array_merge($new_rules, $rules);
    }
}
