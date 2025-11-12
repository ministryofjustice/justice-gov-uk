<?php

namespace DeliciousBrains\WP_Offload_Media\Tweaks;

use Amazon_S3_And_CloudFront;
use Exception;
use Roots\WPConfig\Config;

/**
 * Amazon S3 and CloudFront - assets.
 *
 * This class contains functions related to serving build assets via Amazon S3 and CloudFront.
 */

class AmazonS3AndCloudFrontAssets
{
    private string $image_tag = '';
    private string $transient_key;
    private string $home_host;
    private string $summary_file = 'build/manifests/summary.jsonl';
    private bool $use_cloudfront_for_assets = false;

    private string $cloudfront_host;
    private array $cloudfront_asset_urls;

    private Amazon_S3_And_CloudFront $as3cf;

    public function __construct()
    {
        // Check if the image tag is set.
        if (empty($_ENV['IMAGE_TAG'])) {
            return;
        }

        if (Config::get('DISABLE_CDN_ASSETS')) {
            return;
        }

        // Get the first 8 chars only.
        $this->image_tag = substr($_ENV['IMAGE_TAG'], 0, 8);
        // Set the transient key - for caching the result of `checkManifestsSummary()`.
        $this->transient_key = "cloudfront_assets_$this->image_tag";
        // Get the home host.
        $this->home_host = parse_url(Config::get('WP_HOME'), PHP_URL_HOST);

        // Get the CloudFront host.
        $this->cloudfront_host = $_ENV['CLOUDFRONT_URL'];
        // Set the scheme/protocol for CloudFront, default to https.
        $cloudfront_scheme = isset($_ENV['CLOUDFRONT_SCHEME']) && $_ENV['CLOUDFRONT_SCHEME'] === 'http' ? 'http' : 'https';
        
        // Set the CloudFront asset URLs.
        // - tagged URL includes the image tag, and should be used for theme assets like css and js.
        // - untagged URL points to 'latest' and is used for other assets like images.
        $this->cloudfront_asset_urls = [
            'tagged' => $cloudfront_scheme . '://' . $this->cloudfront_host . '/build/' . $this->image_tag,
            'untagged' => $cloudfront_scheme . '://' . $this->cloudfront_host . '/build/latest',
        ];

        add_action('as3cf_ready', [$this, 'setAs3cfInstance']);
        add_action('init', [$this, 'init']);
        add_filter('template_directory_uri', [$this, 'filterTemplateDirectoryUri'], 10, 1);
        add_filter('style_loader_src', [$this, 'rewriteSrc'], 10, 2);
        add_filter('script_loader_src', [$this, 'rewriteSrc'], 10, 2);
        add_filter('wp_filterable_script_extra_tag', [$this, 'modifyScriptExtra'], 10, 2);
        add_filter('wp_resource_hints', [$this, 'registerResourceHints'], 10, 2);
    }

    /**
     * Set the Amazon_S3_And_CloudFront instance.
     *
     * @param Amazon_S3_And_CloudFront $as3cf_instance
     * @return void
     */

    public function setAs3cfInstance($as3cf_instance): void
    {
        $this->as3cf = $as3cf_instance;
    }

    /**
     * On init, check if the assets exist on the CDN.
     *
     * Get the result from `checkManifestsSummaryWithCache` and store it in a class property.
     *
     * @return void
     */

    public function init(): void
    {
        $this->use_cloudfront_for_assets = $this->checkManifestsSummaryWithCache();
    }

    /**
     * Find an item in an array.
     *
     * When we upgrade to PHP 8.4, we can use array_any instead.
     *
     * @param array $array
     * @param callable $callback
     *
     * @return mixed
     */

    public function arrayAny($array, $callback)
    {
        foreach ($array as $entry) {
            if (call_user_func($callback, $entry) === true) {
                return true;
            }
        }
        return false;
    }


    /**
     * Verify that assets exist and are accessible via the CDN.
     *
     * @return bool
     */

    public function checkManifestsSummary(): bool
    {
        // Get the provider client. See `amazon-s3-and-cloudfront-pro/classes/providers/storage/aws-provider.php`
        $provider_client = $this->as3cf->get_provider_client($this->as3cf->get_setting('region'));

        // Create a signed S3 URL for the summary file.
        $signed_summary_url = $provider_client->get_object_url($this->as3cf->get_setting('bucket'), $this->summary_file, time() + 300);

        // Make a request to the S3 URL, to check if the assets are available.
        $response = wp_remote_get($signed_summary_url);

        // Check for errors
        if (is_wp_error($response)) {
            error_log($response->get_error_message());
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            error_log("AmazonS3AndCloudFrontAssets->checkManifestsSummary() Bad response. Status code: $status_code");
            return false;
        }

        try {
            // Split the JSONL into an array of lines.
            // $manifest_summary will be an array of strings, each string is a JSON object.
            // Each JSON object represents a build. array_reverse is used to have the newest build first.
            // e.g.
            // [
            //     '{"build":"a1b2c3d4","timestamp":"1729685222"}',
            //     '{"build":"e5f6a7b8","timestamp":"1729685221", "deleteAfter":"1729685223"}',
            //     ...
            // ]
            $manifest_summary = array_reverse(explode("\n", $response['body']));

            // Check if the image tag is in the manifest summary.
            return $this->arrayAny($manifest_summary, function ($line) {
                $object = json_decode($line);
                return $object && $object->build === $this->image_tag && empty($object->deleteAfter);
            });
        } catch (Exception $e) {
            error_log('AmazonS3AndCloudFrontAssets->checkManifestsSummary() There was an error handling the response.');
            error_log($e->getMessage());
        }

        return false;
    }

    /**
     * A wrapper around checkManifestsSummary that caches the result.
     *
     * @return bool
     */

    public function checkManifestsSummaryWithCache(): bool
    {
        $cached_value = get_transient($this->transient_key);

        if (is_int($cached_value)) {
            return !!$cached_value;
        }

        $assets_exist = $this->checkManifestsSummary();

        $expiration = $assets_exist ? 12 * 60 * 60 : 60; // 12 hours or 1 minute.

        set_transient($this->transient_key, (int)$assets_exist, $expiration);

        return  $assets_exist;
    }


    /**
     * When `get_template_directory_uri` is called, filter the URL to use the CDN if applicable.
     *
     * Here, the home url will be replaced with the CloudFront untagged URL.
     * e.g. https://www.justice.gov.uk/app/themes/justice -> https://cdn.www.justice.gov.uk/build/latest/app/themes/justice
     * This URL is suitable for favicons, theme images, etc.
     *
     * In certain cases, we may want to skip the CDN rewrite, e.g. for the webmanifest file, because of CORS issues.
     * In those cases, set the global `$moj_skip_next_cdn_rewrite` to true before calling `get_template_directory_uri()`.
     *
     * @param string $template_directory_uri
     * @return string
     */
    public function filterTemplateDirectoryUri(string $template_directory_uri): string
    {
        if (!$this->use_cloudfront_for_assets) {
            return $template_directory_uri;
        }

        // Check for the global to skip CDN rewrite.
        if (!empty($GLOBALS['moj_skip_next_cdn_rewrite'])) {
            unset($GLOBALS['moj_skip_next_cdn_rewrite']);
            return $template_directory_uri;
        }

        // If the host is not the same as WP_HOME, then return early.
        if (parse_url($template_directory_uri, PHP_URL_HOST) !== $this->home_host) {
            return $template_directory_uri;
        }

        return str_replace(get_home_url(), $this->cloudfront_asset_urls['untagged'], $template_directory_uri);
    }

    /**
     * Rewrite the URL of assets to be served via the CDN.
     *
     * Similar to `filterTemplateDirectoryUri`, but for style and script URLs.
     * We ensure that theme CSS and JS files are served via the tagged CloudFront URL.
     * This means that a certain version of the application, will always get the correct version of the CSS and JS files.
     * e.g. https://www.justice.gov.uk/app/themes/justice -> https://cdn.www.justice.gov.uk/build/abcd1234/app/themes/justice
     * and  https://cdn.www.justice.gov.uk/build/latest/app/themes/justice -> https://cdn.www.justice.gov.uk/build/abcd1234/app/themes/justice
     *
     * @param string $src
     * @param string $handle
     *
     * @return string
     */
    public function rewriteSrc(string $src, string $handle): string
    {
        if (!$this->use_cloudfront_for_assets) {
            return $src;
        }

        // If the host is the same as WP_HOME, then replace with tagged CloudFront URL.
        if (parse_url($src, PHP_URL_HOST) === $this->home_host) {
            return str_replace(get_home_url(), $this->cloudfront_asset_urls['tagged'], $src);
        }

        // If the host is the CloudFront host, replace untagged with tagged URL.
        if (parse_url($src, PHP_URL_HOST) === $this->cloudfront_host) {
            return str_replace($this->cloudfront_asset_urls['untagged'], $this->cloudfront_asset_urls['tagged'], $src);
        }

        return $src;
    }


    /**
     * Register a DNS prefetch tag for the pull domain if rewriting is enabled.
     *
     * @param array  $hints
     * @param string $relation_type
     *
     * @return array
     */
    public function registerResourceHints(array $hints, string $relation_type): array
    {
        if ($this->use_cloudfront_for_assets && 'dns-prefetch' === $relation_type) {
            $hints[] = $this->cloudfront_host;
        }

        return $hints;
    }


    /**
     * Modify script localization for certain scripts to update URLs to use CloudFront.
     *
     * Some scripts will use WordPress localization to add inline scripts that contain URLs.
     * Here, we replace those URLs with the tagged CloudFront URL.
     *
     * @param string $value  The original extra script content.
     * @param string $handle The handle of the script the extra script is attached to.
     * @return string The modified extra script content.
     */
    public function modifyScriptExtra($value, $handle)
    {
        if (!$this->use_cloudfront_for_assets) {
            return $value;
        }

        if (in_array($handle, ['thickbox', 'zxcvbn-async'], true)) {
            $search = get_home_url(null, '/wp/wp-includes/js');
            $search_escaped = str_replace('/', '\/', $search);

            $replace = $this->cloudfront_asset_urls['tagged'] . '/wp/wp-includes/js';
            $replace_escaped = str_replace('/', '\/', $replace);

            $value = str_replace($search_escaped, $replace_escaped, $value);
        }

        return $value;
    }
}

new AmazonS3AndCloudFrontAssets();
