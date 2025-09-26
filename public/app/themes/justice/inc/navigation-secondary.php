<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class NavigationSecondary
{
    const CACHE_KEY = 'moj:justice:navigation_secondary:items';

    /**
     * PostMeta instance.
     */
    public PostMeta|null $post_meta = null;

    /**
     * Add hooks for the secondary navigation.
     *
     * @return void
     */
    public function addHooks(): void
    {
        // Clear the cached menu items when a post is saved.
        add_action('save_post_page', function ($post_id) {
            // If the post is a revision, return early.
            if (wp_is_post_revision($post_id)) {
                return;
            }
            // Clear the cache for the secondary navigation items.
            delete_transient(self::CACHE_KEY);
        });
    
        // Prevent the procedure-rules page slug being edited - it's a seed page for the secondary navigation.
        add_filter('wp_unique_post_slug', function ($slug, $post_ID, $post_status, $post_type) {
            if ($post_type === 'page' && $post_ID === get_page_by_path('courts/procedure-rules')->ID) {
                return 'procedure-rules';
            }
            return $slug;
        }, 10, 4);
    }


    /**
     * Get navigation items for the current page.
     *
     * This function retrieves the navigation items for the current page,
     * unlike `getAllPagesForNavigation`, this function returns the items with
     * active and expanded properties set based on the current page.
     *
     * @return array An array of navigation items for the current page.
     */
    public function getCurrentPageNavigation(): array | null
    {
        $items = $this->getAllPagesForNavigation();

        // Add the active and expanded properties based on the current page.
        $this->markActiveAndExpanded($items, get_permalink(get_the_ID()));
        // Remove any items that are hidden and not expanded.
        $this->removeHiddenItems($items);

        return $items;
    }


    /**
     * Get all pages for the secondary navigation.
     *
     * This function returns an array of pages formatted for the secondary navigation.
     * It retrieves the pages from the cache if available, otherwise it populates the items array.
     *
     * The returned value does not include active or expanded properties, since these vary depending on the user's current page.
     * This means that the value for this function can be cached once and reused across requests.
     *
     * @return array An array of pages formatted for the secondary navigation.
     */
    public function getAllPagesForNavigation(): array
    {
        // Get the items from the cache.
        $items = get_transient(self::CACHE_KEY);
        $items = false;

        // If the items are cached, return them.
        if ($items !== false) {
            return $items;
        }

        // Procedure rules is a seed page for the secondary navigation, get it's ID here.
        $procedure_rules_id = get_page_by_path('courts/procedure-rules')->ID;

        // The items aren't cached, so populate the items array.
        $items = [
            [
                'id' => "www-gov-uk-government-organisations-hm-courts-and-tribunals-service",
                'label' => 'Courts',
                'url' => 'https://www.gov.uk/government/organisations/hm-courts-and-tribunals-service',
            ],
            [
                'id' => "procedure-rules-$procedure_rules_id",
                'label' => 'Procedure rules',
                'url' => get_permalink($procedure_rules_id),
                'children' => $this->getChildPagesForNavigation($procedure_rules_id),
            ],
            [
                'id' => "www-gov-uk-government-organisations-hm-prison-and-probation-service",
                'label' => 'Offenders',
                'url' => 'https://www.gov.uk/government/organisations/hm-prison-and-probation-service',
            ],
        ];

        // Set the items in the cache for 12 hours.
        set_transient(self::CACHE_KEY, $items, 12 * HOUR_IN_SECONDS);

        return $items;
    }


    /**
     * Get the child pages for a given post ID, formatted for navigation.
     *
     * @param int $post_id The ID of the parent post.
     * @return array An array of child pages formatted for navigation.
     */
    public function getChildPagesForNavigation($post_id): array
    {
        if ($this->post_meta === null) {
            $this->post_meta = new PostMeta();
        }

        $child_query_args = array(
            'order'  => 'ASC',
            'orderby' => 'menu_order',
            'post_parent' => $post_id,
            'post_type'   => 'page',
            'posts_per_page' => -1,
        );

        $child_query = new \WP_Query($child_query_args);
        $child_posts = $child_query->get_posts();
        wp_reset_postdata();

        return array_map(function ($post) {
            // Get the `_dynamic_menu_exclude_this` metadata value for the page.
            $exclude_this = get_post_meta($post->ID, '_dynamic_menu_exclude_this', true);
            return [
                // Unique ID for use in the `aria-controls` attribute.
                'id' => sanitize_title($this->post_meta->getShortTitle($post->ID)) . "-{$post->ID}",
                // The URL for the page.
                'url' => get_permalink($post->ID),
                // The label for the page, using the short title.
                'label' => $this->post_meta->getShortTitle($post->ID),
                // Is the page hidden from the menu, this is a meta property set on the page.
                'hidden' => $exclude_this === '1',
                // Recursively call the function to get children for this post.
                'children' => $this->getChildPagesForNavigation($post->ID) ?: [],
            ];
        }, $child_posts);
    }


    /**
     * Mark the active and expanded items in the navigation.
     *
     * This function recursively traverses the navigation items and marks the item with the given URL as active.
     * It also marks the parent items as expanded if any of their children are active.
     *
     * Note that this function modifies the items array in place.
     *
     * @param array $items The navigation items to traverse.
     * @param string $url The URL to match against the navigation items.
     * @return bool Returns true if the URL was found and marked as active, false otherwise.
     */
    public function markActiveAndExpanded(array &$items, string $url): bool
    {
        foreach ($items as &$item) {
            if ($item['url'] === $url) {
                $item['active'] = true;
                $item['expanded'] = true;
                return true;
            }
            if (isset($item['children'])) {
                $url_match  = $this->markActiveAndExpanded($item['children'], $url);
                if ($url_match) {
                    $item['expanded'] = true; // Set expanded to true for the parent item
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Recursively remove items that are hidden and not expanded.
     *
     * Note that this function modifies the items array in place.
     *
     * @param array $items The navigation items to traverse.
     * @return void
     */
    public function removeHiddenItems(array &$items): void
    {
        // Loop through each item in the items array.
        foreach ($items as $key => &$item) {
            if (($item['hidden'] ?? false) && !($item['expanded'] ?? false)) {
                // If the item is hidden and not expanded, remove it from the array.
                unset($items[$key]);
            }
            if (!empty($item['children'])) {
                // Recursively call the function on the children.
                $this->removeHiddenItems($item['children']);
            }
        }

        // Re-index the array to have sequential keys.
        $items = array_values($items);
    }
}
