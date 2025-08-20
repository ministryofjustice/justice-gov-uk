<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class DynamicMenu
{
    /**
     * PostMeta instance.
     */
    public PostMeta $post_meta;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->post_meta = new PostMeta();
    }

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

    public function getExcludedChildPagesTags(): array
    {

        $tag_ids = array_map(
            function ($slug): int | null {
                // Map the slug to id.
                $term = get_term_by('slug', $slug, 'post_tag');
                return $term ? $term->term_id : null;
            },
            $this->excluded_child_pages_tags
        );

        return array_filter($tag_ids, fn($id) => $id !== null);
    }


    public function getChildrenForNavigation($post_id): array
    {
        $child_query_args = array(
            'order'  => 'ASC',
            'orderby' => 'menu_order',
            'post_parent' => $post_id,
            'post_type'   => 'page',
            'posts_per_page' => -1,
        );

        // Get the child pages of the Procedure rules page.
        $child_query = new \WP_Query($child_query_args);
        $child_posts = $child_query->get_posts();
        wp_reset_postdata();

        return array_map(function ($post) {
            // error_log('hidden: ' . gettype( get_post_meta($post->ID, '_dynamic_menu_exclude_this', true)));
            // error_log('hidden: ' .  get_post_meta($post->ID, '_dynamic_menu_exclude_this', true));
            $exclude_this = get_post_meta($post->ID, '_dynamic_menu_exclude_this', true);
            return [
                'label' => $this->post_meta->getShortTitle($post->ID),
                'url' => get_permalink($post->ID),
                'children' => $this->getChildrenForNavigation($post->ID) ?: [],
                'hidden' => $exclude_this == true || $exclude_this === '',
            ];
        }, $child_posts);
    }

    /**
     * getTheNavigation
     * Get the entries as an array.
     */

    public function getTheNavigation2(): array | null
    {
        // Properties : url
        // label
        // active
        // children

        function findItemIndexesByUrl(array &$items, string $url): bool
        {
            foreach ($items as  &$item) {
                if ($item['url'] === $url) {
                    $item['active'] = true;
                    $item['expanded'] = true;
                    return true;
                }
                if (isset($item['children'])) {
                    $url_match  = findItemIndexesByUrl($item['children'], $url);
                    if ($url_match) {
                        $item['expanded'] = true; // Set expanded to true for the parent item
                        return true;
                    }
                }
            }
            return false;
        }

        function removeHiddenAndUpdateActiveItems(array &$items): void
        {

            foreach ($items as $key => &$item) {
                if (($item['hidden'] ?? false) && !($item['expanded'] ?? false)) {

                    unset($items[$key]);
                } 
                if (!empty($item['children'])) {
                    removeHiddenAndUpdateActiveItems($item['children']);
                }
            }

            $items = array_values($items); // Re-index the array
        }

        $items = get_transient('moj_justice_dynamic_menu');

        if ($items !== false) {
            error_log('Using cached dynamic menu');
            findItemIndexesByUrl($items, get_permalink(get_the_ID()));
            removeHiddenAndUpdateActiveItems($items);
            return $items;
        }

        $items = [
            // Courts
            [
                'label' => 'Courts',
                'url' => 'https://www.gov.uk/government/organisations/hm-courts-and-tribunals-service',
            ],
            // Procedure rules
            [
                'label' => 'Procedure rules',
                'url' => '/courts/procedure-rules',
                'expanded' => true,
                // 'active' => true,
                'children' => $this->getChildrenForNavigation(get_page_by_path('courts/procedure-rules')->ID),
            ],
            // Offenders
            [
                'label' => 'Offenders',
                'url' => 'https://www.gov.uk/government/organisations/hm-prison-and-probation-service',
            ],
        ];

        set_transient('moj_justice_dynamic_menu', $items, 12 * HOUR_IN_SECONDS);


        findItemIndexesByUrl($items, get_permalink(get_the_ID()));
        removeHiddenAndUpdateActiveItems($items);

        return $items;
    }

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
            foreach ($ancestor_ids as $i => $ancestor_id) {
                $entries[] = [
                    'level' => 1,
                    'title' => $post_meta->getShortTitle($ancestor_id),
                    'url' => get_permalink($ancestor_id),
                    'ancestor' => $location === 'sidebar' && $i >= 2,
                ];
            }
        }

        // Child pages
        $child_query_args = array(
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

        $child_query = new \WP_Query($child_query_args);
        $child_posts = $child_query->get_posts();
        wp_reset_postdata();


        if (!sizeof($child_posts)) {
            // Sibling pages
            $sibling_query_args = [
                'post_type' => 'page',
                'post_parent' => $post->post_parent,
                'posts_per_page' => -1,
                'order' => 'ASC',
                'orderby' => 'menu_order',
                // Exclude pages with the tags in $excluded_child_pages_tags.
                'tax_query' => [
                    [
                        'taxonomy' => 'post_tag',
                        'field' => 'slug',
                        'operator' => 'NOT IN',
                        'terms' => $this->excluded_child_pages_tags,
                    ],
                ],
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
            ];

            $sibling_query = new \WP_Query($sibling_query_args);
            $sibling_posts = $sibling_query->get_posts();
            wp_reset_postdata();

            foreach ($sibling_posts as $sibling_post) {
                $entries[] = [
                    // 'level' => 2,
                    'title' => $post_meta->getShortTitle($sibling_post->ID),
                    'url' => get_permalink($sibling_post->ID),
                    // Only use the selected property on the sidebar.
                    'selected' => $location === 'sidebar' && $sibling_post->ID === $post->ID
                ];
            }
        }

        if (sizeof($child_posts)) {
            // Current page
            $entries[] = [
                'level' => 1,
                'title' => $post_meta->getShortTitle($post->ID),
                'url' => get_permalink(),
                // Only use the selected property on the sidebar.
                'selected' => $location === 'sidebar'
            ];
        }


        // On mobile duplicate the first entry (with some changes).
        if ('mobile-nav' === $location) {
            $first_entry = array_merge($entries[0], [
                'level' => 0,
                'url' => null,
            ]);

            array_unshift($entries, $first_entry);
        }


        // Child pages
        foreach ($child_posts as $post) {
            $entries[] = [
                'level' => 2,
                'title' => $post_meta->getShortTitle($post->ID),
                'url' => get_permalink($post->ID)
            ];
        }


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
