<?php

namespace MOJ;

// Do not allow access outside WP
defined('ABSPATH') || exit;

/**
 * This class is related to WP_CLI commands content quality.
 *
 * Usage:
 * - wp content-quality exclude-news
 */

use WP_CLI;

class ContentQualityCommands
{
    /**
     * Invoke method, for when the command is called.
     */
    public function __invoke($args): void
    {
        error_reporting(0);

        switch ($args[0] ?? '') {
            case 'exclude-news':
                WP_CLI::log('Will do something with the content quality issues, excluding news pages.');

                // Get all page IDs, where the metadata _content_quality_exclude is not set.
                $get_all_page_ids = new \WP_Query([
                    'post_type' => 'page',
                    'fields' => 'ids',
                    'meta_query' => [
                        'relation' => 'OR',
                        [
                            'key' => '_content_quality_exclude',
                            'compare' => 'NOT EXISTS',
                        ],
                        [
                            'key' => '_content_quality_exclude',
                            'value' => 0,
                            'compare' => '=',
                        ],
                    ],
                    'nopaging' => true,
                    'post_status' => ['publish', 'private'],
                ]);

                // WP_CLI::log('IDS: ' . implode(', ', $get_all_page_ids->posts));
                // WP_CLI::log('Count: ' . count($get_all_page_ids->posts));

                // Loop through all posts, and if it is a news post, set the metadata _content_quality_exclude to 1.
                foreach ($get_all_page_ids->posts as $page_id) {
                    $path = parse_url(get_permalink($page_id), PHP_URL_PATH);
                    if (preg_match('/^\/news(-\d+)?\//', $path)) {
                        // If the path starts with /news/ or /news-<number>/,
                        // set the metadata _content_quality_exclude to 1.
                        WP_CLI::log('Excluding page ' . $page_id . ' with path ' . $path);
                        update_post_meta($page_id, '_content_quality_exclude', 1);
                    } else {
                        WP_CLI::log('Not excluding page ' . $page_id . ' with path ' . $path);
                        update_post_meta($page_id, '_content_quality_exclude', 0);
                    }
                }

                break;

            default:
                WP_CLI::log('ContentQuality command not recognized');
                break;
        }
    }
}



if (defined('WP_CLI') && WP_CLI) {
    $cluster_helper_commands = new ContentQualityCommands();
    // 1. Register the instance for the callable parameter.
    WP_CLI::add_command('content-quality', $cluster_helper_commands);

    // 2. Register object as a function for the callable parameter.
    WP_CLI::add_command('content-quality', 'MOJ\ContentQualityCommands');
}
