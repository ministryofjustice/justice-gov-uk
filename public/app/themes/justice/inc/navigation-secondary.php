<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class NavigationSecondary
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
            $exclude_this = get_post_meta($post->ID, '_dynamic_menu_exclude_this', true);
            return [
                // Unique ID for use in the `aria-controls` attribute.
                'id' => sanitize_title($this->post_meta->getShortTitle($post->ID)) . "-{$post->ID}",
                'label' => $this->post_meta->getShortTitle($post->ID),
                'url' => get_permalink($post->ID),
                'children' => $this->getChildrenForNavigation($post->ID) ?: [],
                'hidden' => $exclude_this == true || $exclude_this === '',
            ];
        }, $child_posts);
    }

    /**
     * getTheNavigationLinks
     * Get the entries as an array, for navigation-secondary.twig.
     */

    public function getTheNavigation(): array | null
    {
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

        if (0 && $items !== false) {
            error_log('Using cached dynamic menu');
            findItemIndexesByUrl($items, get_permalink(get_the_ID()));
            removeHiddenAndUpdateActiveItems($items);
            return $items;
        }

        $items = [
            // Courts
            [
                // Unique ID for use in the `aria-controls` attribute.
                'id' => "www-gov-uk-government-organisations-hm-courts-and-tribunals-service",
                'label' => 'Courts',
                'url' => 'https://www.gov.uk/government/organisations/hm-courts-and-tribunals-service',
            ],
            // Procedure rules
            [
                // Unique ID for use in the `aria-controls` attribute.
                'id' => "procedure-rules-" . get_page_by_path('courts/procedure-rules')->ID,
                'label' => 'Procedure rules',
                'url' => '/courts/procedure-rules',
                'expanded' => true,
                // 'active' => true,
                'children' => $this->getChildrenForNavigation(get_page_by_path('courts/procedure-rules')->ID),
            ],
            // Offenders
            [
                // Unique ID for use in the `aria-controls` attribute.
                'id' => "www-gov-uk-government-organisations-hm-prison-and-probation-service",
                'label' => 'Offenders',
                'url' => 'https://www.gov.uk/government/organisations/hm-prison-and-probation-service',
            ],
        ];

        set_transient('moj_justice_dynamic_menu', $items, 12 * HOUR_IN_SECONDS);


        findItemIndexesByUrl($items, get_permalink(get_the_ID()));
        removeHiddenAndUpdateActiveItems($items);

        return $items;
    }
}
