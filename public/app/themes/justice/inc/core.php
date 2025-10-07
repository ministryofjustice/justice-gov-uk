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
        // Remove Available Tools from the admin menu.
        add_action('admin_menu', [$this, 'removeSubmenus']);
        // Remove the Gravatar service, and always show the default avatar.
        add_filter('pre_get_avatar', [__CLASS__, 'replaceGravatar'], 10, 3);

        // Remove inline css blocks...

        // Dequeue the core block styles in the footer.
        add_action('wp_footer', fn () => wp_dequeue_style('core-block-supports'));
        // Remove the global styles that are added by WordPress.
        remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
        // Remove auto-sizes style that's been added by WordPress.
        add_filter('wp_img_tag_add_auto_sizes', '__return_false');
        // Remove the classic theme styles.
        add_action('wp_enqueue_scripts', fn() => wp_dequeue_style('classic-theme-styles'), 20);

        // Remove inline script blocks...


        // Remove the customizer script.
        add_action('add_admin_bar_menus', function () {
            remove_action('admin_bar_menu', 'wp_admin_bar_customize_menu', 40);
        }, 100);

        // Remove the emoji detection script.
        add_action('admin_print_scripts', function () {
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
        }, 1);
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
     * @param false|array|WP_Error $response
     * @param array $parsed_args
     * @param string $url
     * @return false|array|WP_Error
     */

    public function handleLoopbackRequests(false|array|WP_Error $response, array $parsed_args, string $url): false|array|WP_Error
    {
        $loopback_url = Config::get('WP_LOOPBACK');

        // Do we have a loopback url?
        if (empty($loopback_url)) {
            return $response;
        }

        // Is the request url to the application host?
        if (parse_url($url, PHP_URL_HOST) !== parse_url(get_home_url(), PHP_URL_HOST)) {
            return $response;
        }

        // Replace the URL.
        $new_url = str_replace(get_home_url(), $loopback_url, $url);

        // We don't need to verify ssl, calling a trusted container.
        $parsed_args['sslverify'] = false;

        // Get an instance of WP_Http.
        $http = _wp_http_get_object();

        // Return the result.
        return $http->request($new_url, $parsed_args);
    }

    /**
     * Remove Available Tools from the admin menu.
     *
     * @return void
     */

    public function removeSubmenus(): void
    {
        remove_submenu_page('tools.php', 'tools.php');
    }


    /**
     * Replace Gravatar with a default avatar.
     *
     * This way we are not exposing the website's users to the Gravatar service.
     *
     * @param string|false $avatar
     * @param int|string|WP_User $id_or_email
     * @param array $args
     * @return string
     */
    public static function replaceGravatar($_avatar, $_id_or_email, $args) : string
    {
        $width = isset($args['width']) && is_int($args['width']) ? $args['width'] : 26;
        $height = isset($args['height']) && is_int($args['height']) ? $args['height'] : 26;

        return sprintf('<img class="avatar avatar-26 photo" width="%d" height="%d" src="/app/themes/justice/dist/img/avatar.jpg" />', $width, $height);
    }
}
