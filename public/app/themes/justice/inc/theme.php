<?php

namespace MOJ\Justice;

use Roots\WPConfig\Config;

defined('ABSPATH') || exit;

/**
 * A php class related to theme support.
 */

class Theme
{
    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks(): void
    {
        add_action('template_redirect', [$this, 'setFrontendVersionCookieFromQuery'], 1);
        add_action('template_redirect', [$this, 'rolloutFrontendVersionCookie'], 2);

        add_action('after_setup_theme', [$this, 'addThemeSupport']);
        add_filter('site_transient_update_themes', [$this, 'disableThemeUpdateNotification']);
    }


    /**
     * If there is a query string for frontend_version  then set a cookie so that the user
     * persists with the new theme version.
     *
     * @return void
     */
    public static function setFrontendVersionCookieFromQuery(): void
    {
        if (empty($_GET['frontend_version']) || !in_array($_GET['frontend_version'], ['1', '2'], true)) {
            return;
        }

        $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        setcookie('frontend_version', $_GET['frontend_version'], 0, COOKIEPATH, COOKIE_DOMAIN, $https, true);

        // Redirect to the homepage.
        wp_redirect('/');
        exit;
    }

    /**
     * Rollout the frontend version cookie.
     *
     * If the user does not have a frontend_version cookie set, then set it based on the
     * FRONTEND_ROLLOUT_PERCENTAGE setting.
     *
     * @return void
     */
    public static function rolloutFrontendVersionCookie(): void
    {
        // If the cookie is already set, do nothing.
        if (isset($_COOKIE['frontend_version'])) {
            return;
        }

        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (preg_match('/bot|crawl|slurp|spider/i', $user_agent)) {
            // If the user agent is a bot, do not set the cookie.
            return;
        }

        // Get the permalink, if we do set a cookie, we will redirect to this page.
        $permalink = get_permalink();

        // If we are not on a page with a permalink, do nothing.
        if ($permalink === false) {
            return;
        }

        // Get the HTTPS status.
        $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        // If the user is logged in, set the cookie to version 2, show all admins and editors the new version.
        if (is_user_logged_in()) {
            setcookie('frontend_version', '2', 0, COOKIEPATH, COOKIE_DOMAIN, $https, true);
            return;
        }

        // If the user is not logged in, work out the version, based on the rollout percentage.
        $rollout_percentage = Config::get('FRONTEND_ROLLOUT_PERCENTAGE');

        if (mt_rand(1, 100) <= $rollout_percentage) {
            $version = 2;
        } else {
            $version = 1;
        }

        // Set the cookie...
        setcookie('frontend_version', (string) $version, 0, COOKIEPATH, COOKIE_DOMAIN, $https, true);

        // Redirect to the page with a query so that the response is not cached by nginx.
        wp_redirect($permalink . '?redirected');
    }


    /**
     * Add theme support for title-tag.
     *
     * @see https://developer.wordpress.org/reference/functions/add_theme_support/
     *
     * @return void
     */
    public function addThemeSupport(): void
    {
        add_theme_support('title-tag');
    }


    /**
     * Disable update notifications for the Justice Theme.
     *
     * The Justice theme shares a name with a published theme at a higher version so Wordpress reports that our theme needs updating.
     * This will prevent the warning from showing.
     *
     * @param mixed $transient A transient containing the pending theme update information
     *
     * @return mixed The unmodified transient or the updated transient if it's an object
     */
    public function disableThemeUpdateNotification($transient): mixed
    {
        if (isset($transient) && is_object($transient)) {
            unset($transient->response['justice']);
        }
        return $transient;
    }
}
