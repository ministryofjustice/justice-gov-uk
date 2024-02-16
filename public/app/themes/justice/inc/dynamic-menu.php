<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class DynamicMenu
{

    /**
     * Get the entries as an array
     */
    public function getTheNavigation(string $location = 'sidebar'): array | null
    {
        global $post;

        $entries = [];

        $meta = new Meta();
    
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
                    'title' => $meta->getShortTitle($ancestor_id),
                    'url' => get_permalink($ancestor_id)
                ];
            }
        }
        
        // Current page
        $entries[] = [
            'level' => 1,
            'title' => $meta->getShortTitle($post->ID),
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

        // Child pages
        $children = get_pages('parent=' . $post->ID . '&sort_column=menu_order');
        
        // Child page loop
        if ($children) {
            foreach ($children as $child) {
                $entries[] = [
                    'level' => 2,
                    'title' => $meta->getShortTitle($child->ID),
                    'url' => get_permalink($child->ID)
                ];
            }
        }
        
        return $entries;
    }
}
