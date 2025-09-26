<?php

namespace MOJ\Justice;

use Roots\WPConfig\Config;
use WP_Post;

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
        add_filter('the_content', [__CLASS__, 'fixNationalArchiveLinks']);
        add_filter('wp_kses_allowed_html', [__CLASS__, 'customWpksesPostTags'], 10, 2);
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
    public static function fixNationalArchiveLinks($content)
    {

        if (Config::get('WP_ENVIRONMENT_TYPE') === 'staging') {
            // Match strings that start with https://webarchive.nationalarchives.gov.uk any amount words and / then ://stage
            $pattern = '/(https:\/\/webarchive\.nationalarchives\.gov\.uk)([\w\/]*)(:\/\/stage)/';
            return preg_replace($pattern, '$1$2://www', $content);
        }

        return $content;
    }


    /**
     * Customizes the allowed HTML tags for post content.
     * 
     * @param array $tags The allowed HTML tags.
     * @param string $context The context in which the tags are being used.
     * @return array The modified allowed HTML tags.
     */
    public static function customWpksesPostTags($tags, $context)
    {

        if ('post' === $context) {
            // Allow the input tag, for 
            $tags['input'] = array(
                'id'             => true,
                'class'          => true,
                'name'           => true,
                'type'     => true,
                'value' => true,
            );

            // Remove iframe tags to prevent embedding of external content
            if (isset($tags['iframe'])) {
                unset($tags['iframe']);
            }
        }

        return $tags;
    }


    /**
     * Retrieves the content of a post with blocks parsed.
     *
     * This function ensures that the content is processed with block parsing,
     * which is necessary for pages that use the block editor.
     * 
     * @param int|null $post_id The ID of the post to retrieve content for. If null, uses the current post ID.
     * @return string The content of the post with blocks parsed.
     */
    public static function getContentWithBlocks($post_id = null): string
    {
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        $original_global_post = $GLOBALS['post'] ?? null;
        $GLOBALS['post'] = WP_Post::get_instance($post_id); // Override to ensure block parsing.
        $content = apply_filters('the_content', get_the_content(null, false, $post_id));
        $GLOBALS['post'] = $original_global_post; // Restore the original post.
        unset($original_global_post);

        return $content;
    }
}
