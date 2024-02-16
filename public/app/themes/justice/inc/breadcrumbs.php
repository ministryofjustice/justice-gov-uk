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

        $breadcrumbs = [];
    
        // Home page
        $breadcrumbs[] = [
            'title' =>  'Home',
            'url' =>  get_home_url(),
        ];
    
        // Parent page(s)
        if ($post->post_parent) {
            // If child page, get parents
            $anc = get_post_ancestors($post->ID);
                        
            // Get parents in the right order
            $anc = array_reverse($anc);
                    
            // Parent page loop
            foreach ($anc as $ancestor) {
                $breadcrumbs[] = [
                    'title' => get_the_title($ancestor),
                    'url' => get_permalink($ancestor)
                ];
            }
        }
    
        // Current page
        $breadcrumbs[] = [
            'title' =>  get_the_title(),
            'url' => null,
            'last' => true
        ];
    
        return $breadcrumbs;
    }
}
