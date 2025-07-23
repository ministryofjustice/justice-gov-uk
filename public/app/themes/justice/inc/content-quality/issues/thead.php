<?php

/**
 * This class is for content quality checks and reports.
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

final class ContentQualityIssueThead extends ContentQualityIssue
{
    const ISSUE_NAME = 'thead';

    const ISSUE_DESCRIPTION = 'Table without header section';


    /**
     * Load the pages with thead issues.
     * 
     * This function runs an SQL query to find pages with tables that do not have a <thead> element.
     * 
     * @return void
     */
    public function loadPagesWithIssues(): void
    {
        if (null !== $this->pages_with_issue) {
            // Already loaded.
            return;
        }

        // Run an SQL query to find pages with tables that do not have a <thead> element.
        global $wpdb;

        // The following query uses a hack to count occurrences of <table> and <thead> in the post_content.
        // To count the occurrences of a substring in a string, we use a statement following the pattern:
        // CHAR_LENGTH(<haystack>) - CHAR_LENGTH(REPLACE(<haystack>, <needle>, SPACE(LENGTH(<needle>)-1)))

        $query = "
            SELECT 
                ID,
                post_title,
                post_status,
                CHAR_LENGTH(post_content) - CHAR_LENGTH( REPLACE ( post_content, '<table', SPACE(LENGTH('<table')-1) ) ) AS table_count,
                CHAR_LENGTH(post_content) - CHAR_LENGTH( REPLACE ( post_content, '<thead', SPACE(LENGTH('<thead')-1) ) ) AS thead_count
            FROM {$wpdb->posts}
            WHERE 
                post_type = 'page' AND
                post_content LIKE '%<table%' AND
                CHAR_LENGTH(post_content) - CHAR_LENGTH( REPLACE ( post_content, '<table', SPACE(LENGTH('<table')-1)) ) > 
                CHAR_LENGTH(post_content) - CHAR_LENGTH( REPLACE ( post_content, '<thead', SPACE(LENGTH('<thead')-1)) )
            ORDER BY table_count DESC
        ";

        foreach ($wpdb->get_results($query) as $page) {
            // Add the page to the pages with issue.
            $this->pages_with_issue[$page->ID] = (object)[
                'ID' => $page->ID,
                'table_without_thead' => $page->table_count - $page->thead_count,
            ];
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

        $count = $this->pages_with_issue[$post_id]->table_without_thead;

        $issues[] =  sprintf(_n('There is %d table without a header section', 'There are %d tables without a header section', $count, 'justice'), $count);

        return $issues;
    }
}
