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
        'doc', 'docx', 'pdf', 'zip', 'xls', 'xlsx'
    ];

    // Max filesize for wp-document-revisions to stream via php.
    // 10000000 = 10MB.
    private $php_stream_max_filesize = 20000000;

    public function __construct()
    {
        $this->addHooks();
        $this->removeHooks();
    }

    public function addHooks()
    {
        add_action('admin_init', [$this, 'hideEditor']);
        add_filter('document_serve_use_gzip', [$this, 'filterGzip'], null, 3);
        add_filter('document_serve', [$this, 'maybeRedirectToCdn'], null, 3);
        add_filter('as3cf_object_meta', [$this,  'addObjectMeta'], 10, 4);
    }

    /**
     * Remove the editor for the document post type.
     */

    public function hideEditor()
    {
        remove_post_type_support('document', 'editor');
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
     * isDocumentAttachment
     * Check if the attachment is attached to a document post.
     */

    public function isDocumentAttachment($attach_id): bool
    {
        $post_parent = wp_get_post_parent_id($attach_id);
        $parent_post_type = get_post_type($post_parent);

        return $parent_post_type && $parent_post_type === 'document' ? true : false;
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
     * maybeRedirectToCdn
     * Stream the file via php, or redirect the user to the CDN.
     */

    function maybeRedirectToCdn($file, $post_id, $attach_id)
    {

        // Only redirect published files to the CDN.
        if (get_post_status($post_id) !== 'publish') {
            return $file;
        }

        // Get the filesize. 
        $file_size = filesize(get_attached_file($attach_id));

        // If it's too big then redirect to the CDN.
        if ($file_size > $this->php_stream_max_filesize) {
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
     * Vai the Media Library or the Document post type.
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

        // If filename is hex & 32 chars long, then set $content_disposition_filename.
        if (preg_match('/^[a-f0-9]{32}$/', $pathinfo['filename'])) {

            // Get the document ID, permalink and filename.
            $document_id = wp_get_post_parent_id($attach_id);
            $document_permalink = get_permalink($document_id);
            $document_filename = pathinfo($document_permalink, PATHINFO_FILENAME);

            // Build a basename from the document filename with the file extension.
            $content_disposition_basename = $document_filename . '.' . $pathinfo['extension'];
            // Write a filename to S3 metadata. This is what the downloaded file will be called.
            $args['ContentDisposition'] .= ';filename="' . $content_disposition_basename . '"';
        }

        return $args;
    }

}
