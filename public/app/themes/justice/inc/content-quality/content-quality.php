<?php

/**
 * This class is for content quality checks.
 */
namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'traits/columns.php';
require_once 'traits/dashboard-widget.php';
require_once 'traits/filters.php';
require_once 'traits/options.php';

require_once 'issues/anchor.php';
require_once 'issues/email-href.php';
require_once 'issues/email-text.php';
require_once 'issues/empty-heading.php';
require_once 'issues/external-resource.php';
require_once 'issues/incomplete-thead.php';
require_once 'issues/spelling.php';
require_once 'issues/thead.php';

class ContentQuality
{
    use DashboardWidget;
    use OptionsPage;
    use PageColumns;
    use PageFilters;

    public string $slug = 'page';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->addHooks();
        $this->addIssues();
    }


    /**
     * Add hooks for content quality checks.
     *
     * This function adds the necessary hooks for content quality checks, including dashboard widgets,
     * columns in the admin screen, and filters for the pages admin screen.
     *
     * @return void
     */
    public function addHooks(): void
    {
        // Dashboard widget.
        add_action('wp_dashboard_setup', [$this, 'addDashboardWidget']);
        // Columns.
        add_filter('manage_' . $this->slug . '_posts_columns', [$this, 'addColumns']);
        add_filter('manage_' . $this->slug . '_posts_custom_column', [$this, 'addColumnContent'], null, 2);
        // Filters.
        add_action('restrict_manage_posts', [$this, 'addFilteringDropdown']);
        add_filter('parse_query', [$this, 'editorFiltering']);
        // Options page.
        add_action('admin_menu', [$this, 'addOptionsPage']);
        // Create a 1 minute schedule
        add_filter('cron_schedules', [$this, 'addOneMinuteCronSchedule']);
    }

    /**
     * Add content quality issues.
     *
     * This function initializes the content quality issues that will be checked on pages.
     * It creates instances of the ContentQualityIssue classes, which will handle specific content quality checks.
     *
     * @return void
     */
    public function addIssues(): void
    {
        // Add the issues.
        new ContentQualityIssueAnchor();
        new ContentQualityIssueEmailHref();
        new ContentQualityIssueEmailText();
        new ContentQualityIssueEmptyHeading();
        new ContentQualityIssueExternalResource();
        new ContentQualityIssueIncompleteThead();
        new ContentQualityIssueSpelling();
        new ContentQualityIssueThead();

        // Add more issues here as needed.
        // e.g. new ContentQualityIssueAltText();
        // e.g. new ContentQualityIssueEmptyHeadings();
    }


    /**
     * Adds a custom cron schedule of 1 minute.
     *
     * @param array $schedules
     * @return array
     */
    public function addOneMinuteCronSchedule(array $schedules): array
    {
        $schedules['one_minute'] = [
            'interval' => 60,
            'display' => 'Every Minute'
        ];

        return $schedules;
    }
}
