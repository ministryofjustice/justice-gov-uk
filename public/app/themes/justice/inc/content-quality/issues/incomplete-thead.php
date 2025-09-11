<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

/**
 * This class is for content quality checks related to empty table header tags.
 *
 * It extends the ContentQualityIssue class and provides primary methods to identify (and load) pages with incomplete table headers.
 * It includes helper methods to get incomplete table headers from content, and check is a string is all whitespace characters.
 *
 * Even though the class is named `ContentQualityIssueIncompleteThead`, it checks for incomplete table headers in the first row of the table,
 * regardless of the element names. This means that it works for tables with the following structures:
 * - `thead` and `th`
 * - `tr` and `td`
 * - `tr` and `th`
 */
final class ContentQualityIssueIncompleteThead extends ContentQualityIssue
{
    const ISSUE_SLUG = 'incomplete-thead';

    const ISSUE_LABEL = 'Incomplete table header';


    /**
     * Get the pages with empty table header issues.
     *
     * This function retrieves pages with incomplete table header issues from cache only.
     *
     * @return array An array of pages with empty heading issues.
     */
    public function getPagesWithIssues(): array
    {
        $pages_with_issue = [];

        global $wpdb;

        $query = "
            SELECT
                ID,
                COALESCE(options.option_value, 'queued') AS issue_count
            FROM {$wpdb->posts} AS p
            -- To save us from running get_transient in a php loop, 
            -- we can join the options table to get the transient value here
            LEFT JOIN {$wpdb->options} AS options 
                ON options.option_name = CONCAT('_transient_moj:content-quality:issue:incomplete-thead:', ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta
                ON postmeta.post_id = ID AND postmeta.meta_key = '_content_quality_exclude'
            -- Where clauses
            WHERE
                ( options.option_value IS NULL OR options.option_value != '0' ) AND
                post_type = 'page' AND
                p.post_status IN ('publish', 'private', 'draft') AND
                (postmeta.meta_value IS NULL OR postmeta.meta_value = 0)

        ";

        foreach ($wpdb->get_results($query) as $page) {
            // Add the page to the pages with issue.
            $pages_with_issue[$page->ID] = $page->issue_count;
        }

        return $pages_with_issue;
    }


    /**
     * Process pages to identify empty table header issues.
     *
     * This function runs an SQL query to find pages with table tags.
     * It checks if the is an empty cell on the top row of the tables.
     *
     * @return void
     */
    public function processPages(): void
    {
        $transient_updates = [];

        global $wpdb;

        $query = "
            SELECT
                ID,
                -- Only return the post content if it contains an mailto string
                CASE 
                    WHEN p.post_content LIKE '%<table%' THEN p.post_content 
                    ELSE NULL 
                END AS post_content,
                -- Check if the post content contains an mailto string
                IFNULL(p.post_content LIKE '%<table%', 0) AS contains_target_string
            FROM {$wpdb->posts} AS p
            -- To save us from running get_transient in a php loop, 
            -- we can join the options table to get the transient value here
            LEFT JOIN {$wpdb->options} AS options 
                ON options.option_name = CONCAT('_transient_moj:content-quality:issue:incomplete-thead:', ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta
                ON postmeta.post_id = ID AND postmeta.meta_key = '_content_quality_exclude'
            -- Where clauses
            WHERE
                options.option_value IS NULL AND
                post_type = 'page' AND
                p.post_status IN ('publish', 'private', 'draft') AND
                (postmeta.meta_value IS NULL OR postmeta.meta_value = 0)
        ";

        foreach ($wpdb->get_results($query) as $page) :
            if ($page->contains_target_string == 0) {
                // If the post content does not contain any mailto string, set the transient value to 0.
                $transient_updates["$this->transient_key:{$page->ID}"] = 0;
                continue;
            }

            // Get the number of incomplete table headers from the post content.
            $incomplete_thead_count = $this->getIncompleteTheadFromContent($page->post_content);
            // Add the value to the transient updates array, this will be used in a bulk update later.
            $transient_updates["$this->transient_key:{$page->ID}"] = $incomplete_thead_count;
        endforeach;

        if (sizeof($transient_updates)) {
            $expiry = time() + $this->transient_duration;
            $this->bulkSetTransientInDatabase($transient_updates, $expiry);

            // Individual page transients have been updated, so clear the cache for this issue as a whole.
            delete_transient($this->transient_key);
        }
    }


    /**
     * Append issues for a specific page.
     *
     * This function checks if the page has tables with incomplete headers and appends them to the issues array.
     *
     * @param array $issues The current issues array.
     * @param int $post_id The ID of the post to check.
     * @return array The issues array with the incomplete table header issues appended.
     */
    public function appendPageIssues($issues, $post_id)
    {
        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        if (!isset($this->pages_with_issue[$post_id])) {
            return $issues;
        }

        // If the issue is 'queued', then append the appropriate message.
        if ('queued' === $this->pages_with_issue[$post_id]) {
            $issues[] = __('The page is queued for incomplete header issues.', 'justice');
            return $issues;
        }

        $count = $this->pages_with_issue[$post_id];

        $issues[] =  sprintf(_n('There is %d table with an incomplete header', 'There are %d tables with an incomplete header', $count, 'justice'), $count);

        return $issues;
    }


    /**
     * Get all incomplete table headers from the content.
     *
     * This function checks the content for empty heading tags (h1 - h6) and returns the count of empty headings.
     *
     * @param string $content The content to check.
     * @return int The number of empty headings found in the content.
     */
    public static function getIncompleteTheadFromContent(string $content): int
    {
        if (empty($content)) {
            return 0;
        }

        // Create a new DOMDocument instance to parse the HTML content.
        $dom = new \DOMDocument();

        // Suppress warnings from invalid HTML, this is necessary because the content may not be well-formed HTML.
        // The LIBXML_HTML_NOIMPLIED and LIBXML_HTML_NODEFDTD options prevent the addition of <html> and <body> tags.
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $tables_with_incomplete_thead = 0;

        foreach ($dom->getElementsByTagName('table') as $table) {
            $first_row = $table->getElementsByTagName('tr')->item(0);

            // If there is no first row, continue to the next table.
            if ($first_row === null) {
                continue;
            }

            foreach ($first_row->childNodes as $cell) {
                // If the cell is empty or contains only whitespace, increment the count.
                if (self::isAllWhitespaceChars($cell->textContent)) {
                    $tables_with_incomplete_thead++;
                    continue 2; // Skip to the next table if an empty cell is found.
                }
            }
        }

        return $tables_with_incomplete_thead;
    }


    /**
     * Check if a string contains only whitespace characters.
     *
     * This function checks if the string consists entirely of whitespace characters,
     * including spaces, tabs, and non-breaking spaces.
     *
     * @param string $string The string to check.
     * @return bool True if the string contains only whitespace characters, false otherwise.
     */
    public static function isAllWhitespaceChars($string): bool
    {
        foreach (mb_str_split($string) as $char) {
            if (!\IntlChar::isUWhiteSpace($char)) {
                return false;
            }
        }
        return true;
    }
}
