<?php

/**
 * This class is for content quality checks and reports.
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

class ContentQualityIssueThead extends ContentQualityIssue
{
    CONST ISSUE_NAME = 'thead';

    CONST ISSUE_DESCRIPTION = 'Table without header';

    /**
     * Parse the pages admin screen to filter by this issue.
     * 
     * This function is called when the user selects the "Table without header" filter from the pages admin screen.
     * It loads the pages with issues and sets the query to only include those pages.
     * 
     * @param \WP_Query $query The main query object.
     * @return void
     */
    public function parsePagesAdminScreenQuery($query): void
    {
        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        $page_ids_with_issue = array_map(function ($page) {
            return $page->ID;
        }, $this->pages_with_issue);

        $query->set('post__in', $page_ids_with_issue);

        return;
    }


    /**
     * Load the pages with issues
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

        $this->pages_with_issue = $wpdb->get_results($query);
    }


    /**
     * Check if a page has issues.
     *
     * @param bool $has_issues Whether the page has issues.
     * @param int $post_id The ID of the post to check.
     * @return bool Whether the page has issues.
     */
    public function pageHasIssues(bool $has_issues, int $post_id): bool
    {
        if($has_issues) {
            // If the page already has issues, return true.
            return true;
        }

        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        // Check if the post ID is in the pages with issues.
        foreach ($this->pages_with_issue as $page) {
            if ($page->ID == $post_id) {
                return true;
            }
        }

        // If the post ID is not in the pages with issues, return false.
        return false;
    }


    public function filterDashboardIssues(array $issues)
    {
        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        // Add the pages with issues to the issues array.
        $issues[] = [
            'name' => $this::ISSUE_NAME,
            'description' => $this::ISSUE_DESCRIPTION,
            'count' => count($this->pages_with_issue)
        ];

        return $issues;
    }
}


