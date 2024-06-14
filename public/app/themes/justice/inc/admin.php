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
        add_filter('rest_page_query', [$this, 'increaseDropdownLimit'], 9, 2);
        add_action('admin_head', [$this, 'hideNagsForNonAdmins'], 1);
        add_action('wp_before_admin_bar_render', [$this, 'filterAdminBar']);
        add_filter('admin_body_class', [$this, 'addRoleToAdminBody']);
    }


    public static function enqueueStyles(): void
    {
        wp_enqueue_style('justice-admin-style', get_template_directory_uri() . '/dist/css/admin.min.css');
        wp_enqueue_style('justice-editor-style', get_template_directory_uri() . '/dist/css/editor.min.css');
    }

    public static function removeCustomizer(): void
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
    public static function increaseDropdownLimit($args, $request)
    {
        if (is_user_logged_in()) {
            if ($request->get_query_params()['context'] === 'edit') {
                $args['posts_per_page'] = 2000;
                $args['orderby'] = 'title menu_order';
            }
        }
        return $args;
    }


    /**
     * Hide the update and maintenance nags for non-admins.
     *
     * @see https://developer.wordpress.org/reference/functions/update_nag/
     * @see https://developer.wordpress.org/reference/functions/maintenance_nag/
     *
     * @return void
     */
    public function hideNagsForNonAdmins()
    {
        if (!current_user_can('manage_options')) {
            remove_action('admin_notices', 'update_nag', 3);
            remove_action('admin_notices', 'maintenance_nag', 10);
        }
    }

    /**
     * Filter the admin bar to remove unnecessary items and add a link to the documentation.
     *
     * @return void
     */
    public function filterAdminBar()
    {
        global $wp_admin_bar;

        $all_nodes = $wp_admin_bar->get_nodes();

        $remove_keys = [
            'about',
            'contribute',
            'feedback',
            'learn',
            'support-forums',
            'wporg'
        ];

        foreach ($all_nodes as $key => $val) {
            if (in_array($key, $remove_keys)) {
                $wp_admin_bar->remove_node($key);
            }
        }

        // Update the logo link.
        $logo_node = $wp_admin_bar->get_node('wp-logo');
        $logo_node->href = '/wp/wp-admin';
        $wp_admin_bar->add_node($logo_node);

        // Update the link to the documentation.
        $documentation_node = $wp_admin_bar->get_node('documentation');
        $documentation_node->href = '/docs';
        $wp_admin_bar->add_node($documentation_node);
    }


    /**
     * Add the current user's role(s) to the admin body class.
     *
     * This is useful for targeting specific styles to specific roles.
     *
     * @param string $classes The current body classes.
     * @return string The modified body classes.
     */

    public function addRoleToAdminBody($classes)
    {
        $new_classes = array_map(fn ($class) => 'admin-role-' . $class, wp_get_current_user()->roles);

        return $classes . ' ' . implode(' ', $new_classes);
    }
}
