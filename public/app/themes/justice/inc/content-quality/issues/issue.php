<?php

/**
 
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class ContentQualityIssue
{
    CONST ISSUE_NAME = null;

    public array|null $pages_with_issue = null;

    /**
     * Constructor.
     * 
     * The constructor accepts a parseQuery function, which can be used to filter the query based on specific content quality issues.
     */
    public function __construct()
    {
        if(null === $this::ISSUE_NAME) {
            throw new \Exception('ContentQualityIssue::ISSUE_NAME must be set in the child class.');
        }

        $this->addHooks();
    }

    public function addHooks()
    {
        // Add a filter to check if a single page has issues.
        add_filter('moj_content_quality_page_has_issues', [$this, 'pageHasIssues'], 10, 2);

        // Add a filter to the main query when filtering by this issue.
        add_filter('moj_content_quality_filter_content-quality-issue_' . $this::ISSUE_NAME, [$this, 'parsePagesAdminScreenQuery']);

        add_filter('moj_content_quality_filter_dashboard_issues', [$this, 'filterDashboardIssues']);
    }
}
