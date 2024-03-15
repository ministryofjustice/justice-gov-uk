<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

trait DocumentFilters
{

    public $filters = [
        [
            'label' => 'Attachments',
            'query_key' => 'wpdr-attachment-count',
            'values' => [
                '0' => '0',
                '1+' => '1',
            ]
        ],
        [
            'label' => 'Revisions',
            'query_key' => 'wpdr-revision-count',
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


    /*
     * addFilteringDropdown
     * Create a dropdown for filtering documents by the number of attachments.
     */

    public function addFilteringDropdown(): void
    {
        // We are not on the document post type.
        if (!isset($_GET['post_type']) || $_GET['post_type'] !== $this->slug) {
            return;
        }

        // Loop the filters and render them.
        foreach ($this->filters as $filter) {
            get_template_part('inc/documents/filters-template', null, [
                ...$filter,
                'value' => isset($_GET[$filter['query_key']]) ? $_GET[$filter['query_key']] : '',
            ]);
        }
    }


    /*
     * editorFiltering
     * If a filter has been applied with a dropdown, then filter the documents accordingly.
     */

    public function editorFiltering($query): void
    {

        global $pagenow;

        // We are not on admin page for the document post type.
        if (!isset($_GET['post_type'])
            || $_GET['post_type'] !== $this->slug
            || !is_admin()
            || $pagenow !== 'edit.php'
            || !$query->is_main_query()
        ) {
            return;
        }

        foreach ($this->filters as $filter) {
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

    public function applyParentPageFilter(\WP_Query $query, string $value)
    {
        switch ($value) {
            case '0':
                return $query->query_vars['post_parent__in'] = [0];
            case '1':
                return $query->query_vars['post_parent__not_in'] = [0];
        }
    }

    public function applyAttachmentRevisionCountFilter(\WP_Query $query, string $value, $post_type = 'attachment')
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
                return $query->query_vars['post__not_in'] = $ids;
            case '1':
                return $query->query_vars['post__in'] = $ids;
        }
    }
}
