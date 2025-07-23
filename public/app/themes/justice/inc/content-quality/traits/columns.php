<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * Add columns to the pages admin screen.
 */

trait PageColumns
{

    /**
     * Add columns to the pages admin screen.
     *
     * @param array $columns The columns to add to the documents admin screen.
     * @return array The columns with the new columns added.
     */

    public function addColumns(array $columns): array
    {
        $columns['moj-content-quality'] = 'Content Quality';
        return $columns;
    }

    /**
     * Echo the columns content on the pages admin screen.
     *
     * @param string $column The column to add content to.
     * @param int $post_id The id of the post to add content to.
     * @return void
     */

    public function addColumnContent(string $column, int $post_id): void
    {
        if ($column !== 'moj-content-quality') {
            return;
        }

        $has_issues = apply_filters('moj_content_quality_page_has_issues', false, $post_id);
        
        if (empty($has_issues)) {
            echo '<span class="dashicons dashicons-yes"></span> ' . __('No issues', 'justice');
        } else {
            echo '<span class="dashicons dashicons-warning"></span> ' . __('Issues found', 'justice');
        }
    }
}
