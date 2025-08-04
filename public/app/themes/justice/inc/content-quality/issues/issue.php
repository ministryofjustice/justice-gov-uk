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
    const ISSUE_SLUG = null;

    /**
     * @var string|null The description of the issue.
     * This should be set in the child class.
     */
    const ISSUE_LABEL = null;

    /**
     * @var array|null The pages with this issue.
     * This will be set when loading the pages with issues.
     * It will be an array of *partial* page objects that have this issue.
     */
    public array|null $pages_with_issue = null;

    /**
     * @var string|null The transient key for caching the pages with issues.
     * This will be set when loading the pages with issues.
     * It is used to cache the results of the query to improve performance.
     */
    public string|null $transient_key = null;

    /**
     * @var int The duration for which the transient should be cached.
     * Default is 1 week, but can be overridden in child classes.
     */
    public int $transient_duration = 7 * 24 * 60 * 60; // Default to 1 week in seconds (7 days * 24 hours * 60 minutes * 60 seconds).


    /**
     * Constructor
     */
    public function __construct()
    {
        if (null === $this::ISSUE_SLUG) {
            throw new \Exception('ContentQualityIssue::ISSUE_SLUG must be set in the child class.');
        }

        if (null === $this::ISSUE_LABEL) {
            throw new \Exception('ContentQualityIssue::ISSUE_LABEL must be set in the child class.');
        }

        $this->transient_key = 'moj:content-quality:issue:' . $this::ISSUE_SLUG;

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
        // Add a filter (WordPress filter) to add this issue to the filters (dropdown filter on the page screen).
        add_filter('moj_content_quality_filter_values', [$this, 'appendEntryToFilter']);

        // Add a filter to the main query when filtering by this issue.
        add_filter('moj_content_quality_filter_content-quality-issue_' . $this::ISSUE_SLUG, [$this, 'parsePagesAdminScreenQuery']);

        // Add a filter to check if a single page has issues.
        add_filter('moj_content_quality_page_has_issues', [$this, 'pageHasIssues'], 10, 2);

        // Add a filter to append issues for a specific page.
        add_filter('moj_content_quality_page_get_issues', [$this, 'appendPageIssues'], 10, 2);

        // Add a filter to append this issue to the dashboard issues array.
        add_filter('moj_content_quality_filter_dashboard_issues', [$this, 'appendToDashboardIssues']);

        // Add a filter so that when pages are saved, any issues saved in the transient are cleared.
        add_action('save_post_page', function (int $post_id) {
            // Delete the transient for this issue (if Object Cache is enabled, this will delete the transient from the cache).
            delete_transient($this->transient_key);
            delete_transient("$this->transient_key:{$post_id}");
            // Delete from database too, since come extensions of this class are using that.
            $this->deleteTransientFromDatabase($this->transient_key);
            $this->deleteTransientFromDatabase("$this->transient_key:{$post_id}");
        });
    }


    /**
     * Append an entry to the filter values, on the page admin screen.
     *
     * This function is called when the filter values are being built for the pages admin screen.
     * For a given extension of ContentQualityIssue, it will append the issue name and slug to the filter values.
     *
     * @param array $filter_entries The current filter entries.
     * @return array The modified filter entries with the current issue appended.
     */
    public function appendEntryToFilter(array $filter_entries): array
    {
        // Add the issue to the filter entries.
        $filter_entries[$this::ISSUE_LABEL] = $this::ISSUE_SLUG;

        return $filter_entries;
    }


    /**
     * Load the pages with issues into the $this->pages_with_issue property.
     *
     * This function checks if the pages_with_issue property is already set.
     * And, it checks if the value is stored in a transient.
     * If the value is cached, it uses it. Otherwise, it runs the getPages
     */
    public function loadPagesWithIssues(): void
    {
        // If the pages_with_issue property is already set, return early.
        if (null !== $this->pages_with_issue) {
            // Already loaded.
            return;
        }

        // Check if the value is stored in a transient.
        $cached_pages_with_issue = get_transient($this->transient_key);

        // If the value is cached, use it and return.
        if ($cached_pages_with_issue !== false) {
            // If the value is cached, use it.
            $this->pages_with_issue = $cached_pages_with_issue;
            return;
        }

        // Load the pages with issues.
        $this->pages_with_issue = $this->getPagesWithIssues();

        // Cache the pages with issues for performance.
        set_transient($this->transient_key, $this->pages_with_issue, $this->transient_duration);
    }


    /**
     * Get pages with issues.
     *
     * This function should be implemented in the child classes.
     * It could run an SQL query to find pages with issues and should set the $this->pages_with_issue property.
     *
     * @return array
     */
    public function getPagesWithIssues(): array
    {
        // Intentionally left blank.
        // This function should be implemented in the child classes.
        return [];
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

        $page_ids_with_issue = array_keys($this->pages_with_issue);

        if (empty($page_ids_with_issue)) {
            // Passing an empty array to post__in will return all results.
            // This is not what we want, so we set it to an array with a single value that will never match.
            $page_ids_with_issue = [-1];
        }

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
     * Append issues for a specific page.
     *
     * This function should be implemented in the child classes.
     * It should check if the page has issues and append them to the issues array.
     *
     * @param array $issues The current issues array.
     * @param int $post_id The ID of the post to check.
     * @return array The issues array with the issues for the page appended.
     */
    public function appendPageIssues($issues, $post_id)
    {
        return $issues;
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
            'name' => $this::ISSUE_SLUG,
            'description' => $this::ISSUE_LABEL,
            'count' => count($this->pages_with_issue),
            'ids' => array_keys($this->pages_with_issue),
        ];

        return $issues;
    }

    /**
     * Bulk set transient values in the database.
     *
     * This function is used to set multiple transient values in the database at once.
     * It constructs a single SQL query to insert or update the transient values.
     *
     * @param array $values An associative array of transient keys and their values.
     * @param int $expiry The expiry time for the transients, in seconds epoch format.
     * @return void
     */
    public function bulkSetTransientInDatabase(array $values, int $expiry): void
    {
        global $wpdb;

        $query = "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES ";
        foreach ($values as $key => $value) {
            $query .= $wpdb->prepare(
                "(%s, %s, %s), ",
                "_transient_timeout_$key",
                $expiry,
                'no'
            );
            $query .= $wpdb->prepare(
                "(%s, %s, %s), ",
                "_transient_$key",
                $value,
                'no'
            );
        }
        $query = rtrim($query, ', ');
        $query .= " ON DUPLICATE KEY UPDATE option_value = VALUES(option_value), autoload = VALUES(autoload)";
        $wpdb->query($query);
    }

    /**
     * Delete a transient from the database.
     *
     * This function deletes a transient from the database by removing both the transient and its timeout.
     *
     * @param string $transient_key The key of the transient to delete.
     * @return void
     */
    public function deleteTransientFromDatabase(string $transient_key): void
    {
        global $wpdb;

        $query = $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name = %s OR option_name = %s",
            "_transient_timeout_$transient_key",
            "_transient_$transient_key"
        );

        $wpdb->query($query);
    }
}
