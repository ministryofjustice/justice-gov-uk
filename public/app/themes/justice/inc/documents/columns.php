<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

trait DocumentColumns
{

    public function addColumns($columns): array
    {
        $columns['wpdr-revision-attachment-count'] = 'Revisions / Attachments';
        $columns['wpdr-parent-page'] = 'Parent page';
        return $columns;
    }

    public function truncateString($string, $length = 100, $append = '&hellip;')
    {
        $string = trim($string);

        if (strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0] . $append;
        }

        return $string;
    }

    public function arrayFind($xs, $f)
    {
        foreach ($xs as $x) {
            if (call_user_func($f, $x) === true) {
                return $x;
            }
        }
        return null;
    }

    public function addColumnContent($column, $post_id): void
    {
        if ($column === 'wpdr-revision-attachment-count') {
            // Get attachment count from post id
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
                $attachment_count = $this->arrayFind($results, fn ($row) => $row['post_type'] === 'attachment')['count'];
                $revision_count = $this->arrayFind($results, fn ($row) => $row['post_type'] === 'revision')['count'];
                echo "$revision_count / $attachment_count";
            } else {
                echo '0';
            }
        }

        if ($column === 'wpdr-parent-page') {
            // Get parent from post id
            $post = get_post($post_id);
            $parent_id = $post->post_parent;
            $parent_title = $parent_id ? $this->post_meta->getShortTitle($parent_id) : false;
            $parent_edit_link = $parent_id ? get_edit_post_link($parent_id) : false;

            if ($parent_title) {
                echo sprintf('<a href="%s" title="%s">%s</a>', $parent_edit_link, $parent_title, $this->truncateString($parent_title, 30));
            } else {
                echo 'No';
            }
        }
    }
}
