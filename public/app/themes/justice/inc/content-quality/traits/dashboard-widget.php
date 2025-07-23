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
            __('Content Quality Issues', 'justice'),
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
        $issues = apply_filters('moj_content_quality_filter_dashboard_issues', []);

        echo '<p>This widget will report content quality issues such as missing alt text, empty headings, and tables without the header element.</p>';

        if(empty($issues)) {
            echo '<p>' . __('No content quality issues found.', 'justice') . '</p>';
            return;
        }

        echo '<ul>';
        foreach ($issues as $issue) {
            echo '<li>';
            echo '<a href="' . esc_url(admin_url('edit.php?post_type=page&content-quality-issue=' . $issue['name'])) . '">';
            echo esc_html($issue['description']) . ' (' . $issue['count'] . ')</a>';
            echo '</li>';
        }
        echo '</ul>';
    }
}
