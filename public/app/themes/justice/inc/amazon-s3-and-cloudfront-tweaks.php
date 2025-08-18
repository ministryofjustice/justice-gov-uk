<?php

namespace DeliciousBrains\WP_Offload_Media\Tweaks;

use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use DeliciousBrains\WP_Offload_Media\Providers\Storage\AWS_Provider;

defined('ABSPATH') || exit;

class AmazonS3AndCloudFrontTweaks
{
    // This static property holds the instance of the amazon-s3-and-cloudfront plugin.
    private static $as3cf = null;

    public function __construct()
    {
        /*
         * Set the as3cf as a property.
         */
        add_action('as3cf_ready', fn($as3cf) => self::$as3cf = $as3cf, 10, 1);

        /*
         * Update Content-Disposition for documents, when the permalink is updated.
         * This is necessary to ensure that the document is downloaded with the correct filename.
         */
        add_action('document_permalink_updated', function ($document_id) {
            // Ensure the as3cf instance is set before proceeding.
            if (self::$as3cf) {
                $this->updateContentDisposition($document_id);
                return;
            }

            // We don't have self::$as3cf, so add an action to run updateContentDisposition when as3cf is ready.
            add_action('as3cf_ready', function () use ($document_id) {
                $this->updateContentDisposition($document_id);
            }, 20);
        });
    }


    /**
     * This function updates the Content-Disposition header for a document attachment.
     *
     * It uses the AWS S3 client to copy the object with the new Content-Disposition metadata.
     * This is necessary to ensure that the document is downloaded with the correct filename.
     *
     * @param int $document_id The ID of the document to update.
     * @return void
     */
    public function updateContentDisposition($document_id): void
    {
        global $wpdr;

        $attachments = $wpdr->get_attachments($document_id);
        $latest_attachment = reset($attachments);

        if (! $latest_attachment) {
            return;
        }

        $as3cf_item = Media_Library_Item::get_by_source_id($latest_attachment->ID);

        if (! $as3cf_item) {
            return;
        }

        // Use reflection to get access to the private property, AWS_Provider->s3_client
        // This is not supported by the plugin, :-0
        // https://www.lambda-out-loud.com/posts/accessing-private-properties-php/#reflection-slow-but-clean
        $provider = self::$as3cf->get_provider_client($as3cf_item->region());
        $reflectionProperty = new \ReflectionProperty(AWS_Provider::class, 's3_client');
        $reflectionProperty->setAccessible(true);
        $s3_client = $reflectionProperty->getValue($provider);

        $bucket = $as3cf_item->bucket();
        $key = $as3cf_item->provider_key();
        $filename = basename(get_permalink($document_id));

        // Use COPY on the $s3_client to update the Content Disposition metadata,
        // since PUT cannot be used for partial updates.
        $s3_client->copyObject([
            'Bucket' => $bucket,
            'CopySource' => "{$bucket}/{$key}",
            'Key' => $key,
            'MetadataDirective' => 'REPLACE',
            'ContentDisposition' => 'attachment;filename="' . $filename . '"'
        ]);
    }
}

new AmazonS3AndCloudFrontTweaks();
