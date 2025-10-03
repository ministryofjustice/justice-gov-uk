<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add filters to the documents admin screen.
 */

trait DocumentFilters
{
    // CPT slug. This is hardcoded in the plugin.
    const SLUG = 'document';

    /**
     * @var array $FILTERS The filters to add to the documents admin screen.
     */

    const FILTERS = [
        [
            'label' => 'Revisions',
            'query_key' => 'wpdr-revision-count',
            'values' => [
                '0' => '0',
                '1+' => '1',
            ]
        ],
        [
            'label' => 'Attachments',
            'query_key' => 'wpdr-attachment-count',
            'values' => [
                '0' => '0',
                '1+' => '1',
            ]
        ],
        [
            'label' => 'Parent page',
            'query_key' => 'wpdr-parent-page',
            'values' => [
                'No' => '0',
                'Yes' => '1',
            ],
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
        if (!isset($_GET['post_type']) || $_GET['post_type'] !== self::SLUG) {
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
     * Filters the documents, according to selected filters.
     *
     * @global string $pagenow
     *
     * @param \WP_Query $query
     * @return void
     */

    public function editorFiltering($query): void
    {

        global $pagenow;

        // We are not on admin page for the document post type.
        if (!isset($_GET['post_type'])
            || $_GET['post_type'] !== self::SLUG
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
            $value = $_GET[$query_key];
            if ($query_key === 'wpdr-parent-page') {
                $this->applyParentPageFilter($query, $value);
            }
            if ($query_key === 'wpdr-attachment-count') {
                $this->applyAttachmentRevisionCountFilter($query, $value, 'attachment');
            }
            if ($query_key === 'wpdr-revision-count') {
                $this->applyAttachmentRevisionCountFilter($query, $value, 'revision');
            }
        }
    }

    /**
     * Apply a filter to the query. Do documents have a parent post or not?
     *
     * @param \WP_Query &$query
     * @param string $value The value of the filter. 0: No, 1: Yes.
     * @return void
     */

    public function applyParentPageFilter(\WP_Query &$query, string $value) : void
    {
        switch ($value) {
            case '0':
                $query->query_vars['post_parent__in'] = [0];
                return;
            case '1':
                $query->query_vars['post_parent__not_in'] = [0];
                return;
        }
    }

    /**
     * Apply a filter to the query. Do documents have a an attachment or revision?
     *
     * @global \wpdb $wpdb
     *
     * @param \WP_Query &$query
     * @param string $value The value of the filter. 0: No, 1: Yes.
     * @param string $post_type The post type to filter on. Default: 'attachment'.
     * @return void
     */

    public function applyAttachmentRevisionCountFilter(\WP_Query &$query, string $value, $post_type = 'attachment') : void
    {

        // Get the ids of all posts with attachments/revisions.
        global $wpdb;
        $sql = $wpdb->prepare("
            SELECT post_parent AS id
            FROM wp_posts AS p
            WHERE p.post_type = %s
            AND p.post_parent != '0'
            GROUP BY post_parent
        ", $post_type);
        $results = $wpdb->get_results($sql);

        // To array of ids
        $ids = array_map(fn ($post) => $post->id, $results);

        switch ($value) {
            case '0':
                $query->query_vars['post__not_in'] = $ids;
                return;
            case '1':
                $query->query_vars['post__in'] = $ids;
                return;
        }
    }
}
