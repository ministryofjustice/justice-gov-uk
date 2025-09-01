<?php

/**
 * This class is for content quality checks and reports.
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

final class ContentQualityIssueUrlText extends ContentQualityIssue
{
    const ISSUE_SLUG = 'url-text';

    const ISSUE_LABEL = 'Links with a URL for the text';


    /**
     * Get the pages with inaccessible UTL text issues.
     *
     * This function runs an SQL query to find pages with `>http...</a` or `>https...</a` strings.
     *
     * @return array An array of pages with URL link text issues.
     */
    public function getPagesWithIssues(): array
    {
        $pages_with_issue = [];
        $transient_updates = [];

        global $wpdb;

        $query = "
            SELECT ID, post_content, post_modified, options.option_value AS inaccessible_url_text_count
            FROM {$wpdb->posts}
            -- To save us from running get_transient in a php loop, 
            -- we can join the options table to get the transient value here
            LEFT JOIN {$wpdb->options} AS options 
            ON options.option_name = CONCAT('_transient_moj:content-quality:issue:url-text:', ID)
            -- Where clauses
            WHERE
                -- options value should be null or not 0
                ( options.option_value IS NULL OR options.option_value != '0' ) AND
                -- Post type should be page 
                post_type = 'page' AND
                -- Post content should contain a closing element tag (>)
                -- Followed by optional whitespace
                -- Followed by a URL that contains https:// or http://
                -- e.g. <a href='https://example.com'>Example</a> should not match
                -- <a href='https://example.com'>https://example.com</a> should match
                post_content RLIKE '>\s*(https?)'
        ";

        foreach ($wpdb->get_results($query) as $page) :
            $inaccessible_url_text_count = is_null($page->inaccessible_url_text_count) ? null : (int)$page->inaccessible_url_text_count;

            if (is_null($inaccessible_url_text_count)) {
                // The table didn't contain a transient value, so we need to check the content.
                $inaccessible_url_text_count = $this->getInaccessibleUrlLinksFromContent($page->post_content);
                // Add the value to the transient updates array, this will be used in a bulk update later.
                $transient_updates["$this->transient_key:{$page->ID}"] = $inaccessible_url_text_count;
            }

            // If the value is > 0, add it to the pages_with_issue array.
            if ($inaccessible_url_text_count) {
                $pages_with_issue[$page->ID] = $inaccessible_url_text_count;
            }
        endforeach;

        if (sizeof($transient_updates)) {
            $expiry = time() + $this->transient_duration;
            $this->bulkSetTransientInDatabase($transient_updates, $expiry);
        }

        return $pages_with_issue;
    }


    /**
     * Append issues for a specific page.
     *
     * This function checks if the page has issues and appends them to the issues array.
     *
     * @param array $issues The current issues array.
     * @param int $post_id The ID of the post to check.
     * @return array The issues array with the anchor issues appended.
     */
    public function appendPageIssues($issues, $post_id)
    {
        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        if (empty($this->pages_with_issue[$post_id])) {
            return $issues;
        }

        $count = $this->pages_with_issue[$post_id];

        $issues[] =  sprintf(_n('There is %d link with a URL for the text', 'There are %d URL links with a URL for the text', $count, 'justice'), $count);

        return $issues;
    }

    /**
     * Get inaccessibly formatted URL links found in the content.
     *
     * This function checks for links where the text starts with http or https.
     *
     * @param string $content The content to check.
     * @return int The number of inaccessibly formatted URL links found in the content.
     */
    public static function getInaccessibleUrlLinksFromContent(string $content): int
    {
        if (empty($content)) {
            return 0;
        }

        // Use a regex to find links where the text label starts with http or https.
        // e.g. >https://example.com</a> should match and >Example</a> should not match
        $pattern = '/>\s*(https?:\/\/[^\s<]+)\s*<\/a/i';

        // Find all matches in the content.
        preg_match_all($pattern, $content, $matches);

        // If matches are found, return the count.
        return empty($matches[1]) ? 0 :  count($matches[1]);
    }
}
