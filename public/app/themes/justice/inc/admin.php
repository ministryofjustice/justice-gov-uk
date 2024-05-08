<?php

/**
 * A php class to add admin functionality to the theme
 * Like enqueue the css and js files.
 */

namespace MOJ\Justice;

use WP_REST_Request;

if (!defined('ABSPATH')) {
    die();
}

class Admin
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_menu', [$this, 'removeCustomizer'], 999);
        add_filter('rest_page_query', [$this, 'increaseDropdownLimit'], 2, 20);
    }

    public static function enqueueStyles()
    {
        wp_enqueue_style('justice-admin-style', get_template_directory_uri() . '/dist/css/editor.min.css');
    }

    public static function removeCustomizer()
    {
        // We need this because the submenu's link (key from the array too) will always be generated with the current SERVER_URI in mind.
        $customizer_url = add_query_arg('return', urlencode(remove_query_arg(wp_removable_query_args(), wp_unslash($_SERVER['REQUEST_URI']))), 'customize.php');
        remove_submenu_page('themes.php', $customizer_url);
    }
    
    /**
     * Increase the dropdown limit on the block editor parent page field
     *
     * @param array $args An array of arguments for WP_Query
     * @param WP_REST_Request $request The REST API request
     *
     * @return array $args The modified WP_Query array
     */
    function increaseDropdownLimit($args, $request)
    {
        if (is_user_logged_in()) {
            if ($request->get_query_params()['context'] === 'edit') {
                $args['posts_per_page'] = 2000;
            }
        }
        return $args;
    }
}
