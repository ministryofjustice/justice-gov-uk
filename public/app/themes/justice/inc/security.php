<?php

namespace MOJ\Justice;

use WP_Error;

/**
 * Add a little security for WordPress
 */
class Security
{

    private $wp_version;
    private $hashed_wp_version;

    /**
     * Set properties and run actions.
     */
    public function __construct()
    {
        // Get the WordPress version.
        $this->wp_version = get_bloginfo('version');
        // Hash the WP version number with a salt - let's borrow AUTH_SALT for this.
        // This way a we get a unique hash per WP version but it's not reversible.
        $this->hashed_wp_version =  substr(md5($this->wp_version . AUTH_SALT), 0, 6);

        $this->actions();
    }

    /**
     * Loads up actions that are called when WordPress initialises
     *
     * @return void
     */
    public function actions(): void
    {
        // No generator meta tag in the head
        remove_action('wp_head', 'wp_generator');

        add_filter('redirect_canonical', [$this, 'noRedirect404']);
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('wp_headers', [$this, 'headerMods']);
        add_filter('auth_cookie_expiration', [$this, 'setLoginPeriod'], 10, 0);

        // Handle malformed URLs with arrays in the query string.
        add_filter('login_init', [$this, 'validateLoginRequest'], 10, 0);

        // Remove emoji support.
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Strip the WP version number from enqueued asset URLs.
        add_filter('style_loader_tag', [$this, 'filterAssetQueryString'], 10, 1);
        // change the url with script_loader_tag
        add_filter('script_loader_tag', [$this, 'filterAssetQueryString'], 10, 1);

        // Hide the WP version number from the feeds.
        add_filter('the_generator', '__return_empty_string');

        // Disable REST API for non-logged in users.
        add_filter('rest_authentication_errors', [$this, 'restAuth']);
    }

    /**
     * Prevent WordPress from trying to guess and redirect a 404 page
     *
     * https://developer.wordpress.org/reference/functions/redirect_canonical/
     *
     * @param $redirect_url
     * @return false|mixed
     */
    public function noRedirect404($redirect_url): mixed
    {
        if (is_404()) {
            return false;
        }

        return $redirect_url;
    }

    /**
     * @param $headers
     * @return mixed
     */
    public function headerMods($headers): mixed
    {
        unset($headers['X-Pingback']);

        $headers['X-Powered-By'] = 'Justice Digital';
        return $headers;
    }

    /**
     * Sets the expiration time of the login session cookie
     *
     * Nb. if we can harden access to the login page this value
     * can be extended to a much longer period
     *
     * @return float|int
     */
    public function setLoginPeriod(): float|int
    {
        return 7 * DAY_IN_SECONDS; // Cookies set to expire in 7 days.
    }

    /**
     * Change the URL of the style tag
     *
     * @param $html string The HTML string of a link or script tag.
     * @return string The modified HTML string.
     */

    public function filterAssetQueryString($html): string
    {
        return str_replace('ver=' . $this->wp_version, 'ver=' . $this->hashed_wp_version, $html);
    }


    /**
     * Disable REST API for non-logged in users.
     *
     * @see https://developer.wordpress.org/reference/hooks/rest_authentication_errors/
     *
     * @param WP_Error|null|true $result
     * @return WP_Error|null|true
     */
    public function restAuth(WP_Error|null|true $result): WP_Error|null|true
    {
        // If a previous authentication check was applied,
        // pass that result along without modification.
        if (true === $result || is_wp_error($result)) {
            return $result;
        }

        // No authentication has been performed yet.
        // Return an error if user is not logged in.
        if (! is_user_logged_in()) {
            return new WP_Error(
                'rest_not_logged_in',
                __('You are not currently logged in.'),
                array('status' => 401)
            );
        }

        // Our custom authentication check should have no effect
        // on logged-in requests
        return $result;
    }
}
