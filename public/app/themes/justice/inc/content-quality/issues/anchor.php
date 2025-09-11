<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

/**
 * This class is for content quality checks related to anchor links.
 *
 * It extends the ContentQualityIssue class and provides primary methods to identify (and load) pages with anchor issues.
 * It includes helper methods to get anchors from content, check if the content has an element with a given ID.
 */
final class ContentQualityIssueAnchor extends ContentQualityIssue
{
    const ISSUE_SLUG = 'anchor';

    const ISSUE_LABEL = 'Anchor links without destination';

    /**
     * @var array An array of anchors to ignore.
     * These anchors will not be reported as issues, even if they do not have matching target elements in the post content.
     */
    const ANCHORS_TO_IGNORE = ['', 'top'];


    /**
     * Load the pages with anchor issues.
     *
     * This function retrieves pages with anchor issues from cache only.
     *
     * @return array An array of pages with anchor issues.
     */
    public function getPagesWithIssues(): array
    {
        $pages_with_issue = [];

        // Run an SQL query to find pages with tables that have anchor tags with a `#...` destination.
        global $wpdb;

        $query = "
            SELECT 
                ID,
                COALESCE(options.option_value, 'queued') AS issues
            FROM {$wpdb->posts} AS p
            LEFT JOIN {$wpdb->options} AS options 
                ON options.option_name = CONCAT('_transient_moj:content-quality:issue:anchor:', p.ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta
                ON postmeta.post_id = ID AND postmeta.meta_key = '_content_quality_exclude'
            WHERE 
                ( options.option_value IS NULL OR options.option_value != 'a:0:{}' ) AND
                post_type = 'page' AND 
                p.post_status IN ('publish', 'private', 'draft') AND
                (postmeta.meta_value IS NULL OR postmeta.meta_value = 0)
        ";

        // Loop over every page, and unserialize the value of this page's spelling issues.
        foreach ($wpdb->get_results($query) as $page) :
            $pages_with_issue[$page->ID] = maybe_unserialize($page->issues);
        endforeach;

        return $pages_with_issue;
    }


    /**
     * Process pages for anchor issues.
     *
     * This function runs an SQL query to find pages with anchor tags that have a # destination.
     * It checks if there is an element with a matching ID for each anchor.
     * If there is no matching element, it adds the anchor issue to the transient updates array.
     * Finally, it updates the transients in the database.
     *
     * @return void
     */
    public function processPages(): void
    {
        $transient_updates = [];

        global $wpdb;

        $query = "
            SELECT 
                ID,
                -- Only return the post content if it contains an href=\"#\"
                CASE 
                    WHEN p.post_content LIKE '%href=\"#%' THEN p.post_content 
                    ELSE NULL 
                END AS post_content,
                -- Check if the post content contains an href=\"#\"
                IFNULL(p.post_content LIKE '%href=\"#%', 0) AS contains_href_string
            FROM {$wpdb->posts} AS p
            LEFT JOIN {$wpdb->options} AS options 
                ON options.option_name = CONCAT('_transient_moj:content-quality:issue:anchor:', p.ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta
                ON postmeta.post_id = ID AND postmeta.meta_key = '_content_quality_exclude'
            WHERE 
                ( options.option_value IS NULL ) AND
                post_type = 'page' AND 
                p.post_status IN ('publish', 'private', 'draft') AND
                (postmeta.meta_value IS NULL OR postmeta.meta_value = 0)
        ";

        foreach ($wpdb->get_results($query) as $page) {

            if ($page->contains_href_string == 0) {
                // If the post content does not contain any href="#", set the transient value to an empty array.
                // This will prevent the page from being processed again in the future.
                $transient_updates["$this->transient_key:{$page->ID}"] = serialize([]);
                continue;
            }

            // Keep track of broken anchors for this page.
            $broken_anchors = [];

            // Get all anchors from the content.
            $anchors = $this->getAnchorsFromContent($page->post_content);

            // Loop the anchors from the content.
            foreach ($anchors as $anchor) {
                // In $page->post_content is there an element with matching ID for each anchor?
                if (!$this->contentHasElementWithId($page->post_content, $anchor)) {
                    // If not, add the anchor to the broken anchors array.
                    $broken_anchors[] = $anchor;
                }
            }

            // Add the value to the transient updates array, this will be used in a bulk update later.
            $transient_updates["$this->transient_key:{$page->ID}"] = serialize($broken_anchors);
        }

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
     * This function checks if the page has anchor issues and appends them to the issues array.
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
            $issues[] = __('The page is queued for broken anchor issues.', 'justice');
            return $issues;
        }

        $broken_anchors = $this->pages_with_issue[$post_id];
        $broken_anchors_string = implode(', ', $broken_anchors);
        $count = count($broken_anchors);

        $issues[] =  sprintf(_n('The following anchor is missing a target: %s', 'The following anchors are missing a target: %s', $count, 'justice'), $broken_anchors_string);

        return $issues;
    }


    /**
     * Get all anchors from the content.
     *
     * This function extracts all anchor destinations from the content.
     * It returns an array of unique anchors, excluding those that in the ANCHORS_TO_IGNORE constant.
     *
     * @param string $content The content to check.
     * @return array An array of unique anchors found in the content.
     */
    public static function getAnchorsFromContent(string $content): array
    {
        // Get all of the anchor destinations as an array
        // e.g. ['#', '#section1', '#section2']
        preg_match_all('/href="#([^"]*?)"/', $content, $matches);

        // Filter out the anchors that are just #
        $anchors = array_filter($matches[1], function ($anchor) {
            return !in_array($anchor, self::ANCHORS_TO_IGNORE) && !empty($anchor);
        });

        // Make anchors unique and reset the array keys.
        return array_values(array_unique($anchors));
    }


    /**
     * Check if the content has an element with the given ID.
     *
     * @param string $content The content to check.
     * @param string $id The ID to check for.
     * @return bool Whether the content has an element with the given ID.
     */
    public static function contentHasElementWithId(string $content, string $id): bool
    {
        // Check if the content has an element with the given ID, where the id is wrapped in double quotes.
        if (strpos($content, 'id="' . $id . '"') !== false) {
            return true;
        }
        // Check for single quotes as well.
        if (strpos($content, "id='" . $id . "'") !== false) {
            return true;
        }
        return false;
    }
}
