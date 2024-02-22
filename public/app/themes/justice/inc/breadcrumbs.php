<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class Breadcrumbs
{

    /**
     * Get the breadcrumbs as an array
     */
    public function getTheBreadcrumbs(): array | null
    {
        global $post;

        if (is_home() || is_front_page() || !is_page()) {
            return null;
        }

        $post_meta = new PostMeta();

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
                    'title' => $post_meta->getShortTitle($ancestor_id),
                    'url' => get_permalink($ancestor_id)
                ];
            }
        }
    
        // Current page
        $breadcrumbs[] = [
            'title' => $post_meta->getShortTitle(),
            'url' => null,
            'last' => true
        ];
    
        return $breadcrumbs;
    }
}
