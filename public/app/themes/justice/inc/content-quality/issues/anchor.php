<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

/**
 * This class is for content quality checks related to anchor links.
 *
 * It extends the ContentQualityIssue class and provides a primary methods to identify (and load) pages with anchor issues.
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
     * This function runs an SQL query to find pages with anchor tags that have a # destination.
     * It checks if there is an element with a matching ID for each anchor.
     * If there is no matching element, it adds the page to the $this->pages_with_issue property.
     * 
     * @return void
     */
    public function loadPagesWithIssues(): void
    {
        if (null !== $this->pages_with_issue) {
            // Already loaded.
            return;
        }

        $this->pages_with_issue = [];

        // Run an SQL query to find pages with tables that have anchor tags with a `#...` destination.
        global $wpdb;

        $query = "
            SELECT ID, post_content
            FROM {$wpdb->posts}
            WHERE post_type = 'page' AND post_content LIKE '%href=\"#%'
        ";

        foreach ($wpdb->get_results($query) as $page) {
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

            // If there are broken anchors, add the page to the pages_with_issue array.
            if (!empty($broken_anchors)) {
                $this->pages_with_issue[$page->ID] = (object)[
                    'ID' => $page->ID,
                    'broken_anchors' =>  $broken_anchors
                ];
            }
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

        $broken_anchors = $this->pages_with_issue[$post_id]->broken_anchors;
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
