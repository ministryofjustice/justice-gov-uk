<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * Add filters to the documents admin screen.
 */

trait PageFilters
{

    /**
     * @var array $FILTERS The filters to add to the documents admin screen.
     * @todo - This needs to be dynamic, so that it can be extended by other plugins (or sibling files).
     */
    const FILTERS = [
        [
            'label' => 'Content quality',
            'query_key' => 'content-quality-issue',
            'values' => [
                'Table without header' => 'thead',
                'Anchor without destination' => 'anchor',
            ]
        ]
    ];


    /**
     * Create dropdown for filtering documents.
     *
     * @return void
     */
    public function addFilteringDropdown(): void
    {
        // We are not on the document post type.
        if (!isset($_GET['post_type']) || $_GET['post_type'] !== $this->slug) {
            return;
        }

        // Loop the filters and render them.
        foreach ($this::FILTERS as $filter) {
            get_template_part('inc/documents/filters-template', null, [
                ...$filter,
                'value' => isset($_GET[$filter['query_key']]) ? $_GET[$filter['query_key']] : '',
            ]);
        }
    }


    /**
     * Filters the pages, according to selected filters.
     *
     * @global string $pagenow
     *
     * @param \WP_Query $query
     * @return void
     */
    public function editorFiltering($query): void
    {

        global $pagenow;

        // We are not on admin page for the page post type.
        if (
            !isset($_GET['post_type'])
            || $_GET['post_type'] !== $this->slug
            || !is_admin()
            || $pagenow !== 'edit.php'
            || !$query->is_main_query()
        ) {
            return;
        }

        foreach ($this::FILTERS as $filter) {
            $query_key = $filter['query_key'];
            if (!isset($_GET[$query_key]) || !in_array($_GET[$query_key], $filter['values'])) {
                continue;
            }
            apply_filters(
                'moj_content_quality_filter_' . $query_key . '_' . $_GET[$query_key],
                $query
            );
        }
    }
}
