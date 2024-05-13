<?php

namespace MOJ\Justice;

use WP_Error;
use Roots\WPConfig\Config;

defined('ABSPATH') || exit;

/**
 * Actions and filters related to WordPress core.
 */

class Core
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks(): void
    {
        // Remove the welcome panel.
        add_action('admin_init', [$this, 'removeWelcomePanel']);
        // Remove default dashboard widgets.
        add_action('wp_dashboard_setup', [$this, 'removeDefaultDashboardWidgets']);
        // Disable remote block patterns. Avoids unnecessary transient entry in the database.
        add_filter('should_load_remote_block_patterns', '__return_false');
        // Avoids unnecessary transient entry in the database, by returning an empty array.
        add_filter('translations_api', fn () => []);
        // Handle loopback requests.
        add_filter('pre_http_request', [$this, 'handleLoopbackRequests'], 10, 3);
    }

    /**
     * Remove the welcome panel from the dashboard.
     *
     * @return void
     */

    public function removeWelcomePanel(): void
    {
        remove_action('welcome_panel', 'wp_welcome_panel');
    }

    /**
     * Removes various default dashboard widgets.
     *
     * Some of them create unnecessary transient entries in the database.
     * Others are only relevant to posts, and will likely never be used.
     *
     * @return void
     */

    public function removeDefaultDashboardWidgets(): void
    {
        // Transient entries.
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');      // Right Now/At a Glance
        remove_meta_box('dashboard_primary', 'dashboard', 'side');          // WordPress blog/events
        // Posts.
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');      // Quick Press/Quick Draft
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');       // Activity
    }

    /**
     * Handle loopback requests.
     *
     * Handle requests to the application host, by sending them to the loopback url.
     *
     * @param false|array|WP_Error $preempt
     * @param array $args
     * @param string $url
     * @return false|array|WP_Error
     */

    public function handleLoopbackRequests(false|array|WP_Error $preempt, array $args, string $url): false|array|WP_Error
    {
        $loopback_url = Config::get('WP_LOOPBACK');

        // Do we have a loopback url?
        if (empty($loopback_url)) {
            return $preempt;
        }

        // Is the request url to the application host?
        if (parse_url($url, PHP_URL_HOST) !== parse_url(get_home_url(), PHP_URL_HOST)) {
            return $preempt;
        }

        // Replace the URL.
        $new_url = str_replace(get_home_url(), $loopback_url, $url);

        // We don't need to verify ssl, calling a trusted container.
        $args['sslverify'] = false;

        // Get an instance of WP_Http.
        $http = _wp_http_get_object();

        // Return the result.
        return $http->request($new_url, $args);
    }
}
