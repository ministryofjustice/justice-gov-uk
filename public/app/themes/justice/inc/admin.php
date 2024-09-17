<?php

/**
 * A php class to add admin functionality to the theme
 * Like enqueue the css and js files.
 */

namespace MOJ\Justice;

use Roots\WPConfig\Config;
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
        add_action('admin_enqueue_scripts', array($this, 'loadScripts'));
        add_action('admin_menu', [$this, 'removeCustomizer'], 999);
        add_filter('rest_page_query', [$this, 'increaseDropdownLimit'], 9, 2);
        add_action('admin_head', [$this, 'hideNagsForNonAdmins'], 1);
        add_action('wp_before_admin_bar_render', [$this, 'filterAdminBar']);
        add_filter('admin_body_class', [$this, 'addRoleToAdminBody']);
        add_filter('wp_sentry_public_options', [$this, 'filterSentryJsOptions']);
    }


    public static function enqueueStyles(): void
    {
        wp_enqueue_style('justice-admin-style', get_template_directory_uri() . '/dist/css/admin.min.css');
        wp_enqueue_style('justice-editor-style', get_template_directory_uri() . '/dist/css/editor.min.css');
    }

    /**
     * Load the admin app script.
     *
     * @return void
     */

    public function loadScripts(): void
    {

        $script_asset_path = get_template_directory() . "/dist/php/admin.min.asset.php";

        if (!file_exists($script_asset_path)) {
            throw new \Error(
                'You need to run `npm start` or `npm run build` for "app" first.'
            );
        }

        $script_asset = require($script_asset_path);
        wp_register_script(
            'moj-justice-admin',
            get_template_directory_uri() . '/dist/admin.min.js',
            $script_asset['dependencies'],
            $script_asset['version'],
            [
                // Defer the script to avoid render blocking.
                'defer' => true,
            ]
        );

        wp_enqueue_script('moj-justice-admin');
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

        $support_email = Config::get('SUPPORT_EMAIL');

        $all_nodes = $wp_admin_bar->get_nodes();

        $remove_keys = [
            'about',
            'contribute',
            'documentation',
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

        // Add a link to the HowTo.
        $wp_admin_bar->add_node(
            array(
                'parent' => 'wp-logo-external',
                'id'     => 'howto-admin',
                'title'  => 'HowTo Admin',
                'href'   => 'https://howto-admin.www.justice.gov.uk'
            )
        );

        // Add a link to the support email.
        $wp_admin_bar->add_node(
            array(
                'parent' => 'wp-logo-external',
                'id'     => 'support-link',
                'title'  => 'Support',
                'href'   => 'mailto:' . $support_email
            )
        );

        // Add the support email (for copying to clipboard) to the admin bar.
        $wp_admin_bar->add_node(
            array(
                'parent' => 'wp-logo-external',
                'id'     => 'support-text',
                'title'  => $support_email
            )
        );
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
        $new_classes = array_map(fn($class) => 'admin-role-' . $class, wp_get_current_user()->roles);

        return $classes . ' ' . implode(' ', $new_classes);
    }

    /**
     * Filter the options used by sentry-javascript for `Sentry.init()`
     */

    public function filterSentryJsOptions(array $options)
    {

        // If we're not on an admin page then return early.
        if (!is_admin()) {
            return $options;
        }

        // Add custom settings for admin screens.
        return array_merge($options, array(
            'sendDefaultPii' => true,
            'sampleRate' => 1.0,
            'tracesSampleRate' => 1.0,
            'replaysSessionSampleRate' => 1.0,
            'replaysOnErrorSampleRate' => 1.0,
            'wpSessionReplayOptions' => [
                // To capture additional information such as request and response headers or bodies,
                // you'll need to opt-in via networkDetailAllowUrls
                'networkDetailAllowUrls' => [get_home_url()],
            ]
        ));
    }
}
