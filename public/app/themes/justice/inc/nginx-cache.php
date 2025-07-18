<?php

namespace MOJ\Justice;

use ClusterHelper;

defined('ABSPATH') || exit;

class NginxCache
{
    public function __construct()
    {
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

        // Get all Nginx hosts from the ClusterHelper.
        $cluster_helper = new ClusterHelper();
        $nginx_hosts = $cluster_helper->getNginxHosts('hosts');

        // Loop through each Nginx host and send a purge request.
        foreach ($nginx_hosts as $host) {
            // Construct the full URL for the purge request.
            $nginx_cache_path = $host . '/purge-cache' . $post_path;

            // Purge the cache.
            $result = wp_remote_get($nginx_cache_path, [
                'blocking' => false,
                'headers' => ['Host' => parse_url(home_url(), PHP_URL_HOST)]
            ]);

            // Check for errors in the response.
            if (is_wp_error($result)) {
                error_log(sprintf('Error purging cache at %s: %s', $nginx_cache_path, $result->get_error_message()));
                continue;
            }

            error_log(sprintf('Cache cleared at %s', $nginx_cache_path));
        }
    }
}
