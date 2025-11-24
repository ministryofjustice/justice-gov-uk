<?php

namespace MOJ\Justice;

use Roots\WPConfig\Config;

defined('ABSPATH') || exit;

class NginxCache
{
    private string $cache_purge_url;

    public function __construct()
    {
        $cache_purge_url = Config::get('NGINX_PURGE_CACHE_URL');

        if (empty($cache_purge_url)) {
            // If the NGINX_PURGE_CACHE_URL is not set, we cannot proceed.
            error_log('NGINX_PURGE_CACHE_URL is not set. Cannot purge cache.');
            return;
        }

        $this->cache_purge_url = $cache_purge_url;

        $this->addHooks();
    }

    /**
     * Register the action to clear the Nginx cache when a post is saved or updated.
     */
    public function addHooks()
    {
        add_action('wp_after_insert_post', [$this, 'clearNginxCache'], 10, 2);

        add_action('revisionary_revision_published', [$this, 'clearNginxCache'], 10, 1);
    }

    /**
     * Send a purge cache request to all Nginx servers when a post is saved or updated.
     *
     * @param int|object $post The post object, or ID
     *
     * @return void
     */
    public function clearNginxCache(int|object $post): void
    {
        $post_id = is_object($post) ? $post->ID : $post;

        // Check if the post is a revision or unpublished.
        if (wp_is_post_revision($post_id) || get_post_status($post_id) !== 'publish') {
            return;
        }

        // Get the post URL.
        $post_url = get_permalink($post_id);
        $post_path = parse_url($post_url, PHP_URL_PATH);
        $purge_url = $this->cache_purge_url . $post_path;

        // Purge the cache.
        $result = wp_remote_get($purge_url, [
            'blocking' => false,
            'headers' => ['Host' => parse_url(home_url(), PHP_URL_HOST)]
        ]);

        // Check for errors in the response.
        if (is_wp_error($result)) {
            error_log(sprintf('Error purging cache at %s: %s', $purge_url, $result->get_error_message()));
            return;
        }

        error_log(sprintf('Cache cleared at %s', $purge_url));
    }
}
