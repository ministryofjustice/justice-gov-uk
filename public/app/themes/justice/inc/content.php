<?php

namespace MOJ\Justice;

use Roots\WPConfig\Config;
use WP_Document_Revisions;

defined('ABSPATH') || exit;

/**
 * Actions and filters related to content.
 */

class Content
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks(): void
    {
        add_filter('the_content', [$this, 'fixNationalArchiveLinks']);
        add_filter('body_class', [$this, 'addBodyClassIfContentContainsH1'], 25);
    }

    /**
     * Filter the content to fix broken National Archives links.
     *
     * A fix to replace the dev/demo/stage URL with the www URL when pointing to national archive URLs.
     * This only runs on non-production environments.
     *
     * e.g. It replaces the broken link:
     * https://webarchive.nationalarchives.gov.uk/ukgwa/20211201113600/https://stage.justice.gov.uk/courts/procedure-rules/family/parts/part_02
     * with the working link:
     * https://webarchive.nationalarchives.gov.uk/ukgwa/20211201113600/https://www.justice.gov.uk/courts/procedure-rules/family/parts/part_02
     *
     * @param string $content
     * @return string
     */

    public function fixNationalArchiveLinks($content)
    {

        if (Config::get('WP_ENVIRONMENT_TYPE') === 'staging') {
            // Match strings that start with https://webarchive.nationalarchives.gov.uk any amount words and / then ://stage
            $pattern = '/(https:\/\/webarchive\.nationalarchives\.gov\.uk)([\w\/]*)(:\/\/stage)/';
            return preg_replace($pattern, '$1$2://www', $content);
        }

        return $content;
    }


    /**
     * Add a body class if the content contains an <h1> tag.
     *
     * This is used to add the 'rich-text-contains-h1' class to the body element,
     * which can be used to style the rich text component differently if it contains an <h1> tag.
     *
     * @param array $classes The existing body classes.
     * @return array The modified body classes.
     */
    public function addBodyClassIfContentContainsH1($classes)
    {
        if ((is_single() || is_page()) && is_main_query()) {
            $content = get_the_content();
            if (strpos($content, '<h1') !== false) {
                return [...$classes, 'rich-text-contains-h1'];
            }
        }
        return $classes;
    }


    /**
     * Determine if a link is external or internal so that we can add (opens in new tab)
     *
     * @param string $url The link
     *
     * @return bool True or false depending on whether the link is external or internal
     */
    public function isExternal(string $url): bool
    {
        $components = parse_url($url);
        return !empty($components['host']) && strcasecmp($components['host'], $_SERVER['HTTP_HOST']);
    }

    /**
     * Returns the formatted filesize from any post_id to display in the file-download component
     *
     * @param int $postId The ID of the attachment post
     * @return string|null The formatted filesize (to the largest byte unit)
     */

    public function getFormattedFilesize(int $postId): string|null
    {
        $filesize = null;
        // Init a WP_Document_Revisions class so that we can use document specific functions
        $document = new WP_Document_Revisions;

        // If this is a document (from wp-document-revisions) get the attachment ID and set the $postId to that
        if ($document->verify_post_type($postId)) {
            $attachment = $document->get_document($postId);
            if ($attachment?->ID) {
                // Update the $postId variable to the document's attachment ID
                $postId = $attachment->ID;
            }
        }

        // Otherwise check the db for the saved filesize
        $postMeta = get_post_meta($postId, '_wp_attachment_metadata', true);
        // Prefer the original filesize
        if (!empty($postMeta['filesize']) && is_int($postMeta['filesize'])) {
            $filesize = $postMeta['filesize'];
        // But if it's offloaded get the size saved by AS3CF
        } else {
            $offloadedFilesize = get_post_meta($postId, 'as3cf_filesize_total', true);
            $filesize = !empty($offloadedFilesize) && is_int($offloadedFilesize) ? $offloadedFilesize : null;
        }
        return size_format($filesize);
    }
}
