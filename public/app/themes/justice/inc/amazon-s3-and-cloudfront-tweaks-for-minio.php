<?php

/**
 * This file is cherry-picked functions from the wp-amazon-s3-and-cloudfront-tweaks plugin.
 * It is the config for using Minio Locally with WP Offload Media.
 * @see http://github.com/deliciousbrains/wp-amazon-s3-and-cloudfront-tweaks
 *
 * When accessing the WP Offload Media Lite setting page,
 * the plugin will log the following errors when trying to access the Minio server:
 * - AS3CF: Could not get Block All Public Access status: Error executing "GetPublicAccessBlock"
 * - AS3CF: Could not get Object Ownership status: Error executing "GetBucketOwnershipControls"
 * This is because Minio does not support these features.
 */

namespace DeliciousBrains\WP_Offload_Media\Tweaks;

defined('ABSPATH') || exit;

class AmazonS3AndCloudFrontTweaksForMinio
{
    public function __construct()
    {
        /*
         * WP Offload Media & WP Offload Media Lite
         *
         * https://deliciousbrains.com/wp-offload-media/
         * https://wordpress.org/plugins/amazon-s3-and-cloudfront/
         */


        /*
         * Custom S3 API Example: MinIO
         * @see https://min.io/
         */
        add_filter('as3cf_aws_s3_client_args', [$this, 'minioS3ClientArgs']);
        add_filter('as3cf_aws_s3_url_domain', [$this, 'minioS3UrlDomain'], 10, 5);
        add_filter('as3cf_aws_s3_console_url', [$this, 'minioS3ConsoleUrl']);
        add_filter('as3cf_aws_s3_console_url_prefix_param', [$this, 'minioS3ConsoleUrlPrefixParam']);

        /*
         * URL Rewrite related filters.
         */
        add_filter('as3cf_get_attachment_url', [$this, 'getAttachmentUrl'], 10, 4);
        add_filter('as3cf_use_ssl', '__return_false', 10, 1);
    }

    // Define the minio hostnames for the backend and frontend contexts.
    private static $minio_host_in_backend_context = 'minio';
    private static $minio_host_in_frontend_context = 'minio.justice.docker';

    /*
     * >>> MinIO Examples Start
     */

    /**
     * This filter allows you to adjust the arguments passed to the provider's service specific SDK client.
     *
     * The service specific SDK client is created from the initial provider SDK client, and inherits most of its config.
     * The service specific SDK client is re-created more often than the provider SDK client for specific scenarios, so if possible
     * set overrides in the provider client rather than service client for a slight improvement in performance.
     *
     * @see     https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.S3Client.html#___construct
     * @see     https://docs.min.io/docs/how-to-use-aws-sdk-for-php-with-minio-server.html
     *
     * @handles `minioS3ClientArgs`
     *
     * @param array $args
     *
     * @return array
     *
     * Note: A good place for changing 'signature_version', 'use_path_style_endpoint' etc. for specific bucket/object actions.
     */
    public function minioS3ClientArgs($args)
    {
        // Example changes endpoint to connect to a local MinIO server configured to use port 54321 (the default MinIO port is 9000).
        $args['endpoint'] = 'http://' . self::$minio_host_in_backend_context . ':9000';

        // Example forces SDK to use endpoint URLs with bucket name in path rather than domain name as required by MinIO.
        $args['use_path_style_endpoint'] = true;

        return $args;
    }

    /**
     * This filter allows you to change the URL used for serving the files.
     *
     * @handles `minioS3UrlDomain`
     *
     * @param string $domain
     * @param string $bucket
     * @param string $region
     * @param int    $expires
     * @param array  $args Allows you to specify custom URL settings
     *
     * @return string
     */
    public function minioS3UrlDomain($domain, $bucket, $region, $expires, $args)
    {
        // MinIO doesn't need a region prefix, and always puts the bucket in the path.
        return self::$minio_host_in_backend_context . ':9000/' . $bucket;
    }



    /**
     * This filter allows you to change the base URL used to take you to the provider's console from WP Offload Media's settings.
     *
     * @handles `minioS3ConsoleUrl`
     *
     * @param string $url
     *
     * @return string
     */
    public function minioS3ConsoleUrl($url)
    {
        return 'http://' . self::$minio_host_in_frontend_context . ':9001/browser/';
    }

    /**
     * The "prefix param" denotes what should be in the console URL before the path prefix value.
     *
     * For example, the default for AWS/S3 is "?prefix=".
     *
     * The prefix is usually added to the console URL just after the bucket name.
     *
     * @handles `minioS3ConsoleUrlPrefixParam`
     *
     * @param $param
     *
     * @return string
     *
     * MinIO just appends the path prefix directly after the bucket name.
     */
    public function minioS3ConsoleUrlPrefixParam($param)
    {
        return '/';
    }

    /*
     * <<< MinIO Examples End
     */


    /*
     * URL Rewrite related filters.
     */

    /**
     * This filter allows you to change the cloud storage URL for an attachment.
     *
     * @handles `as3cf_get_attachment_url`
     *
     * @param string                                                    $url
     * @param DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item $as3cf_item
     * @param int                                                       $post_id
     * @param int                                                       $expires
     *
     * @return string
     *
     * Note: Runs earlier than `as3cf_wp_get_attachment_url`
     */
    public function getAttachmentUrl($url, $as3cf_item, $post_id, $expires)
    {
        // Replace the hostname for the browser.
        return str_replace(self::$minio_host_in_backend_context, self::$minio_host_in_frontend_context, $url);
    }
}

new AmazonS3AndCloudFrontTweaksForMinio();
