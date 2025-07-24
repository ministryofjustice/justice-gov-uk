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
        $this->hashed_wp_version = substr(hash('sha256', $this->wp_version . AUTH_SALT), 0, 6);

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

        // Return 404 for all author pages.
        add_action('template_redirect', [$this, 'disableAuthorPages'], 1);

        // Remove the "View" link from user admin screen, since these will 404.
        add_filter('user_row_actions', [$this, 'removeViewLinkOnUsersScreen'], PHP_INT_MAX);
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
     * Change the URL of the script or style tags.
     *
     * @see https://developer.wordpress.org/reference/hooks/style_loader_tag/
     *
     * @param $tag string The HTML string of a link or script tag.
     * @return string The modified HTML string.
     */
    public function filterAssetQueryString(string $tag): string
    {
        return str_replace('ver=' . $this->wp_version, 'ver=' . $this->hashed_wp_version, $tag);
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

    /**
     * Disable author pages.
     * 
     * Return status code 404 for existing and non-existing author archives.
     * 
     * @see https://developer.wordpress.org/reference/hooks/template_redirect/
     * @return void
     */
    public function disableAuthorPages(): void
    {
        if (isset($_GET['author']) || is_author()) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
        }
    }

    /**
     * Remove the "View" link from user admin screen.
     *
     * @param string[] $actions An array of action links to be displayed.
     * @return string[] $actions The modified array of action links.
     */
    public function removeViewLinkOnUsersScreen(array $actions): array
    {
        if (isset($actions['view'])) {
            unset($actions['view']);
        }
        return $actions;
    }
}
