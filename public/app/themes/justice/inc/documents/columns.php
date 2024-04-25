<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add columns to the documents admin screen.
 */

trait DocumentColumns
{

    /**
     * Add columns to the documents admin screen.
     *
     * @param array $columns The columns to add to the documents admin screen.
     * @return array The columns with the new columns added.
     */

    public function addColumns(array $columns): array
    {
        $columns['wpdr-revision-attachment-count'] = 'Revisions / Attachments';
        $columns['wpdr-parent-page'] = 'Parent page';
        return $columns;
    }

    /**
     * Echo the columns content on the documents admin screen.
     *
     * @param string $column The column to add content to.
     * @param int $post_id The id of the post to add content to.
     * @return void
     */

    public function addColumnContent(string $column, int $post_id): void
    {
        if ($column === 'wpdr-revision-attachment-count') {
            // Get attachment count from post id.
            global $wpdb;

            $sql = $wpdb->prepare("
            SELECT post_type, count(*) AS count
            FROM wp_posts AS p
            WHERE p.post_type IN ('attachment', 'revision')
            AND p.post_parent = %s
            GROUP BY post_type
            ", $post_id);
            $results = $wpdb->get_results($sql, ARRAY_A);

            if ($results) {
                $attachment_row = $this->utils->arrayFind($results, fn ($row) => $row['post_type'] === 'attachment');
                $attachment_count = $attachment_row ? $attachment_row['count'] : 0;

                $revision_row = $this->utils->arrayFind($results, fn ($row) => $row['post_type'] === 'revision');
                $revision_count = $revision_row ? $revision_row['count'] : 0;

                echo "$revision_count / $attachment_count";
            } else {
                echo '0 / 0';
            }
        }

        if ($column === 'wpdr-parent-page') {
            // Get parent from post id.
            $post = get_post($post_id);
            $parent_id = $post->post_parent;
            $parent_title = $parent_id ? $this->post_meta->getShortTitle($parent_id) : false;
            $parent_edit_link = $parent_id ? get_edit_post_link($parent_id) : false;

            if ($parent_title) {
                echo sprintf('<a href="%s" title="%s">%s</a>', $parent_edit_link, $parent_title, $this->utils->truncateString($parent_title, 30));
            } else {
                echo 'No';
            }
        }
    }
}
