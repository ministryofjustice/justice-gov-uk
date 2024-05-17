<?php

namespace MOJ\Justice;

use WP_Sitemaps_Provider;

defined('ABSPATH') || exit;

/**
 * Sitemap
 * Actions and filters related to the sitemap.
 */

class Sitemap
{
    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks()
    {
        add_filter('wp_sitemaps_add_provider', [$this, 'removeUsersFromSitemap'], 10, 2);
    }

    /**
     * Remove the users sitemap file from wp-sitemap.xml
     *
     * @param WP_Sitemaps_Provider $provider Instance of a WP_Sitemaps_Provider
     * @param string $name Name of the sitemap provider
     *
     * @return bool|WP_Sitemaps_Provider $provider False if the sitemap is users, otherwise return the unmodified provider instance
     */
    public function removeUsersFromSitemap($provider, $name)
    {
        if ('users' === $name) {
            return false;
        }
        return $provider;
    }
}
