<?php

/**
 * This class is for content quality checks and reports.
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

final class ContentQualityIssueThead extends ContentQualityIssue
{
    CONST ISSUE_NAME = 'thead';

    CONST ISSUE_DESCRIPTION = 'Table without header';


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

        $this->pages_with_issue = $wpdb->get_results($query);
    }
}


