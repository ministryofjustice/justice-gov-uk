<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class Breadcrumbs
{

    public function getShortTitle(string | int $post_id): string
    {
        $short_title = get_post_meta($post_id, 'short_title', true);

        return $short_title ? $short_title : get_the_title($post_id);
    }

    /**
     * Get the breadcrumbs as an array
     */
    public function getTheBreadcrumbs(): array | null
    {
        global $post;

        if (is_home() || is_front_page() || !is_page()) {
            return null;
        }

        $breadcrumbs = [];
    
        // Home page
        $breadcrumbs[] = [
            'title' =>  'Home',
            'url' =>  get_home_url(),
        ];
    
        // Parent page(s)
        if ($post->post_parent) {
            // If child page, get parents
            $ancestor_ids = get_post_ancestors($post->ID);
                        
            // Get parents in the right order
            $ancestor_ids = array_reverse($ancestor_ids);

            // Read meta data _short_title
                    
            // Parent page loop
            foreach ($ancestor_ids as $ancestor_id) {
                $breadcrumbs[] = [
                    'title' => $this->getShortTitle($ancestor_id),
                    'url' => get_permalink($ancestor_id)
                ];
            }
        }
    
        // Current page
        $breadcrumbs[] = [
            'title' => $this->getShortTitle($post->ID),
            'url' => null,
            'last' => true
        ];
    
        return $breadcrumbs;
    }
}
