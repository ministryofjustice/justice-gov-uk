<?php


namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * This class is the base class for content quality issues.
 *
 * It should be extended by specific content quality issues, such as ContentQualityIssueAnchor and ContentQualityIssueThead.
 * It provides methods to load pages with issues, check if a page has issues, and append the issue to the dashboard issues array.
 */
class ContentQualityIssue
{
    /**
     * @var string|null The name of the issue.
     * This should be set in the child class.
     */
    const ISSUE_NAME = null;

    /**
     * @var string|null The description of the issue.
     * This should be set in the child class.
     */
    const ISSUE_DESCRIPTION = null;

    /**
     * @var array|null The pages with this issue.
     * This will be set when loading the pages with issues.
     * It will be an array of *partial* page objects that have this issue.
     */
    public array|null $pages_with_issue = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        if (null === $this::ISSUE_NAME) {
            throw new \Exception('ContentQualityIssue::ISSUE_NAME must be set in the child class.');
        }

        if (null === $this::ISSUE_DESCRIPTION) {
            throw new \Exception('ContentQualityIssue::ISSUE_DESCRIPTION must be set in the child class.');
        }

        $this->addHooks();
    }


    /**
     * Add hooks for filters related to content quality.
     * 
     * This function adds the necessary hooks for content quality checks, including dashboard widgets,
     * columns in the admin screen, and filters for the pages admin screen.
     * 
     * @return void
     */
    public function addHooks(): void
    {
        // Add a filter to the main query when filtering by this issue.
        add_filter('moj_content_quality_filter_content-quality-issue_' . $this::ISSUE_NAME, [$this, 'parsePagesAdminScreenQuery']);

        // Add a filter to check if a single page has issues.
        add_filter('moj_content_quality_page_has_issues', [$this, 'pageHasIssues'], 10, 2);


        add_filter('moj_content_quality_page_get_issues', [$this, 'appendPageIssues'], 10, 2);

        // Add a filter to append this issue to the dashboard issues array.
        add_filter('moj_content_quality_filter_dashboard_issues', [$this, 'appendToDashboardIssues']);
    }

    public function appendPageIssues($issues, $post_id)
    {
        return $issues;
    }


    /**
     * Load pages with issues, into the $this->pages_with_issue property.
     * 
     * This function should be implemented in the child classes.
     * It could run an SQL query to find pages with issues and should set the $this->pages_with_issue property.
     * 
     * @return void
     */
    public function loadPagesWithIssues(): void
    {
        // Intentionally left blank.
        // This function should be implemented in the child classes.
    }


    /**
     * Parse the pages admin screen query to filter issue.
     * 
     * This function is called when the user selects a content-quality-issue filter from the pages admin screen.
     * For a given extension of ContentQualityIssue, it loads the pages with the issue and sets the query to only include those pages.
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
     * Check if a page has issues.
     * 
     * For a given extension of ContentQualityIssue, this function will check if the page has issues.
     *
     * @param bool $has_issues Whether the page has issues.
     * @param int $post_id The ID of the post to check.
     * @return bool Whether the page has issues.
     */
    public function pageHasIssues(bool $has_issues, int $post_id): bool
    {
        if ($has_issues) {
            // If the page already has issues, return true.
            return true;
        }

        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        return !empty($this->pages_with_issue[(int)$post_id]);
    }


    /**
     * Append an entry to the dashboard issues array.
     * 
     * This function is called when the dashboard widget is rendered.
     * For a given extension of ContentQualityIssue, it will append the issue name, description, and count of pages with issues.
     * 
     * @param array $issues The current issues array.
     * @return array The modified issues array with the current issue appended.
     */
    public function appendToDashboardIssues(array $issues): array
    {
        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        // Add the pages with issues to the issues array.
        $issues[] = [
            'name' => $this::ISSUE_NAME,
            'description' => $this::ISSUE_DESCRIPTION,
            'count' => count($this->pages_with_issue),
            'ids' => array_keys($this->pages_with_issue),
        ];

        return $issues;
    }
}
