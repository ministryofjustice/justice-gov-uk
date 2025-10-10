<?php

/**
 * This class is for content quality checks and reports.
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

final class ContentQualityIssueThead extends ContentQualityIssue
{
    const ISSUE_SLUG = 'thead';

    const ISSUE_LABEL = 'Table without header section';


    /**
     * Get the pages with thead issues.
     *
     * This function runs an SQL query to find pages with tables that do not have a <thead> element.
     *
     * @return array An array of pages with thead issues.
     */
    public function getPagesWithIssues(): array
    {
        $pages_with_issue = [];

        // Run an SQL query to find pages with tables that do not have a <thead> element.
        global $wpdb;

        // The following query uses a hack to count occurrences of <table> and <thead> in the post_content.
        // To count the occurrences of a substring in a string, we use a statement following the pattern:
        // CHAR_LENGTH(<haystack>) - CHAR_LENGTH(REPLACE(<haystack>, <needle>, SPACE(LENGTH(<needle>)-1)))

        $query = "
            SELECT 
                ID,
                COALESCE(options.option_value, 'queued') AS issue_count
            FROM {$wpdb->posts} AS p
            -- To save us from running get_transient in a php loop, 
            -- we can join the options table to get the transient value here
            LEFT JOIN {$wpdb->options} AS options 
                ON options.option_name = CONCAT('_transient_moj:content-quality:issue:thead:', p.ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta
                ON postmeta.post_id = ID AND postmeta.meta_key = '_content_quality_exclude'
            WHERE
                -- options value should be null or not zero
                ( options.option_value IS NULL OR options.option_value != '0' ) AND
                post_type = 'page' AND
                post_status IN ('publish', 'private', 'draft') AND
                (postmeta.meta_value IS NULL OR postmeta.meta_value = '0')
        ";

        foreach ($wpdb->get_results($query) as $page) {
            // Add the page to the pages with issue.
            $pages_with_issue[$page->ID] = $page->issue_count;
        }

        return $pages_with_issue;
    }

    /**
     * Get the pages with thead issues.
     *
     * This function runs an SQL query to find pages with tables that do not have a <thead> element.
     *
     * @return array An array of pages with thead issues.
     */
    public function processPages(): void
    {
        $transient_updates = [];

        // Run an SQL query to find pages with tables that do not have a <thead> element.
        global $wpdb;

        // The following query uses a hack to count occurrences of <table> and <thead> in the post_content.
        // To count the occurrences of a substring in a string, we use a statement following the pattern:
        // CHAR_LENGTH(<haystack>) - CHAR_LENGTH(REPLACE(<haystack>, <needle>, SPACE(LENGTH(<needle>)-1)))

        $query = "
            SELECT 
                ID,
                ( CHAR_LENGTH(post_content) - CHAR_LENGTH( REPLACE ( post_content, '<table', SPACE(LENGTH('<table')-1) ) ) ) -
                ( CHAR_LENGTH(post_content) - CHAR_LENGTH( REPLACE ( post_content, '<thead', SPACE(LENGTH('<thead')-1) ) ) ) AS tables_without_thead
            FROM {$wpdb->posts} AS p
            LEFT JOIN {$wpdb->options} AS options 
                ON options.option_name = CONCAT('_transient_moj:content-quality:issue:thead:', p.ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta
                ON postmeta.post_id = ID AND postmeta.meta_key = '_content_quality_exclude'
            WHERE 
                options.option_value IS NULL AND
                post_type = 'page' AND
                post_status IN ('publish', 'private', 'draft') AND
                (postmeta.meta_value IS NULL OR postmeta.meta_value = '0')
        ";

        foreach ($wpdb->get_results($query) as $page) :
            // Add the value to the transient updates array, this will be used in a bulk update later.
            $transient_updates["$this->transient_key:{$page->ID}"] = $page->tables_without_thead;
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
     * This function checks if the page has thead issues and appends them to the issues array.
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

        // If the issue is 'queued', then append the appropriate message.
        if ('queued' === $this->pages_with_issue[$post_id]) {
            $issues[] = __('The page is queued for table without a header issues.', 'justice');
            return $issues;
        }

        $count = $this->pages_with_issue[$post_id];

        $issues[] =  sprintf(_n('There is %d table without a header section', 'There are %d tables without a header section', $count, 'justice'), $count);

        return $issues;
    }
}
