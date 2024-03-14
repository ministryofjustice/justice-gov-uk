<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class DynamicMenu
{

    /**
     * List of tags to exclude from the patent page navigation.
     */

    public $excluded_child_pages_tags = [
        'frontmatter-collection',
        'backmatter-collection',
        'parts-collection',
        'content-collection'
    ];

    /**
     * getExcludedChildPagesTags
     * returns an array of ids.
     */

    public function getExcludedChildPagesTags() : array
    {

        $tag_ids = array_map(
            function ($slug) : int | null {
                // Map the slug to id.
                $term = get_term_by('slug', $slug, 'post_tag');
                return $term ? $term->term_id : null;
            },
            $this->excluded_child_pages_tags
        );

        return array_filter($tag_ids, fn($id) => $id !== null);
    }

    /**
     * getTheNavigation
     * Get the entries as an array.
     */

    public function getTheNavigation(string $location = 'sidebar'): array | null
    {
        global $post;

        if (!$post) {
            return null;
        }

        $entries = [];

        $post_meta = new PostMeta();

        // Parent page(s)
        if ($post->post_parent) {
            // If child page, get parents
            $ancestor_ids = get_post_ancestors($post->ID);

            // Get parents in the right order
            $ancestor_ids = array_reverse($ancestor_ids);

            // Parent page loop
            foreach ($ancestor_ids as $ancestor_id) {
                $entries[] = [
                    'level' => 1,
                    'title' => $post_meta->getShortTitle($ancestor_id),
                    'url' => get_permalink($ancestor_id)
                ];
            }
        }

        // Current page
        $entries[] = [
            'level' => 1,
            'title' => $post_meta->getShortTitle($post->ID),
            'url' => get_permalink(),
            // Only use the selected property on the sidebar.
            'selected' => $location === 'sidebar'
        ];

        // On mobile duplicate the first entry (with some changes).
        if ('mobile-nav' === $location) {
            $first_entry = array_merge($entries[0], [
                'level' => 0,
                'url' => null,
            ]);

            array_unshift($entries, $first_entry);
        }

        $query_args = array(
            'order'  => 'ASC',
            'orderby' => 'menu_order',
            'post_parent' => $post->ID,
            'post_type'   => 'page',
            'posts_per_page' => 50,
            // Exclude pages with the tags in $excluded_child_pages_tags.
            'tax_query'   => array(
                array(
                    'taxonomy' => 'post_tag',
                    'field' => 'slug',
                    'operator' => 'NOT IN',
                    'terms' => $this->excluded_child_pages_tags,
                ),
            ),
            // Exclude pages with the _dynamic_menu_exclude_this meta set to true.
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_dynamic_menu_exclude_this',
                    'value' => false,
                    'compare' => '=',
                ),
                array(
                    'key' => '_dynamic_menu_exclude_this',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        // Child pages
        $the_query = new \WP_Query($query_args);
        $posts = $the_query->get_posts();

        foreach ($posts as $post) {
            $entries[] = [
                'level' => 2,
                'title' => $post_meta->getShortTitle($post->ID),
                'url' => get_permalink($post->ID)
            ];
        }

        wp_reset_postdata();

        // Additional entries
        $additional_entries = get_post_meta($post->ID, '_dynamic_menu_additional_entries', true);

        if (!empty($additional_entries)) {
            foreach ($additional_entries as $entry) {
                $entries[] = [
                    'level' => 2,
                    'title' => $entry['label'],
                    'url' => $entry['url']
                ];
            }
        }

        return $entries;
    }
}
