<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class DynamicMenu
{

    private $hidden_child_pages_tags = [
        'frontmatter-collection',
        'backmatter-collection',
        'parts-collection',
        'content-collection'
    ];

    /**
     * Get the entries as an array
     */
    public function getTheNavigation(string $location = 'sidebar'): array | null
    {
        global $post;

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
            'tax_query'   => array(
                array(
                    'taxonomy' => 'post_tag',
                    'field' => 'slug',
                    'operator' => 'NOT IN',
                    'terms' => $this->hidden_child_pages_tags,
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
