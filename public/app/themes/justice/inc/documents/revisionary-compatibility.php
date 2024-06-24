<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add scheduling to the documents post type.
 */

class DocumentRevisionsWithRevisionary
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks(): void
    {
        // Could this be a revisionary hook? So it's not run on every serve request?
        add_filter( 'document_serve_attachment', [$this, 'serveAttachment'], 10, 2 );

        add_filter('revisionary_apply_revision_data', [$this, 'applyRevisionData'], 10, 3);
    }

    public function serveAttachment($attach, $rev_id)
    {
        error_log('In serveAttachment');
        // is the parent of the attachment a document (correct) or a revision (approved revision).
        global $wpdb;
        $post_table = "{$wpdb->prefix}posts";
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $parent = $wpdb->get_var($wpdb->prepare("SELECT post_parent FROM `$post_table` WHERE ID = %d ", $attach->post_parent));
        if (0 !== (int) $parent) {
            // update attachment post.
            error_log('Attachment: ' . $attach->ID . ' has parent: ' . $attach->post_parent .  ' which is a revision. Updating parent to: ' . $parent);
            // wp_update_post(array(
            //     'ID'          => $attach->ID,
            //     'post_parent' => $parent,
            // ));
            // $attach->post_parent = $parent;
        }
        return $attach;
    }

    public function applyRevisionData( $update, $revision, $published) {
        error_log('In applyRevisionData');
        error_log('update: ' . print_r($update, true));
        error_log('revision: ' . print_r($revision, true));
        error_log('published: ' . print_r($published, true));
        return $update;
    }
}
