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

        $issues = apply_filters('moj_content_quality_page_get_issues', [], $post_id);

        get_template_part('inc/content-quality/traits/columns-template', null, ['issues' => $issues]);
    }
}
