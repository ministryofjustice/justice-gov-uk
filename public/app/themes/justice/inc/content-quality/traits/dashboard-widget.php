<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * Add a dashboard widget to report content quality issues.
 */
trait DashboardWidget
{
    /**
     * Add a dashboard widget to report content quality issues.
     *
     * @return void
     */
    public function addDashboardWidget(): void
    {
        wp_add_dashboard_widget(
            'content_quality_widget',
            __('Content Quality', 'justice'),
            [$this, 'renderDashboardWidget']
        );
    }

    /**
     * Render the content quality dashboard widget.
     *
     * @return void
     */
    public function renderDashboardWidget(): void
    {
        // Get the page count.
        $page_count = wp_count_posts('page');

        // Get the issues, each issue class will append an entry to the issues array.
        $issues = apply_filters('moj_content_quality_filter_dashboard_issues', []);
        
        // Count the pages with issues... map the ids, merge them, and count unique ids.
        // This is to avoid counting the same page multiple times if it has multiple issues.
        $pages_with_issues_count = count(array_unique(array_merge(...array_map(fn($issue) => $issue['ids'], $issues))));
        
        // Calculate the count of pages without issues.
        // This is the total pages minus the pages with issues.
        $pages_without_issues_count = $page_count->publish + $page_count->draft - $pages_with_issues_count;

        // Render the dashboard widget template part.
        get_template_part('inc/content-quality/traits/dashboard-widget-template', null, [
            'issues' => $issues,
            'without_issue_count' => $pages_without_issues_count,
            'with_issue_count' => $pages_with_issues_count
        ]);
    }
}
