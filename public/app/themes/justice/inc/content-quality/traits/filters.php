<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * Add filters to the documents admin screen.
 */

trait PageFilters
{
    /**
     * @var array $filter The filter to add to the pages admin screen.
     */
    CONST FILTER = [
        'label' => 'Content quality',
        'query_key' => 'content-quality-issue',
        // The values are added by the issues, they are an associative array with the issue label as the value and the issue slug as the key.
        'values' => []
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

        $filter = [
            ...$this::FILTER,
            'values' => apply_filters(
                'moj_content_quality_filter_values',
                $this::FILTER['values']
            )
        ];

        // Render the filter.
        get_template_part('inc/content-quality/traits/filters-template', null, [
            ...$filter,
            'value' => isset($_GET[$filter['query_key']]) ? $_GET[$filter['query_key']] : '',
        ]);
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

        $filter = [
            ...$this::FILTER,
            'values' => apply_filters(
                'moj_content_quality_filter_values',
                $this::FILTER['values']
            )
        ];

        $query_key = $filter['query_key'];
        if (!isset($_GET[$query_key]) || !in_array($_GET[$query_key], $filter['values'])) {
            return;
        }

        apply_filters(
            'moj_content_quality_filter_' . $query_key . '_' . $_GET[$query_key],
            $query
        );
    }
}
