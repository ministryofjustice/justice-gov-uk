<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

/**
 * This class is for content quality checks related to empty heading tags.
 *
 * It extends the ContentQualityIssue class and provides a ...
 */
final class ContentQualityIssueEmptyHeading extends ContentQualityIssue
{
    const ISSUE_SLUG = 'empty-heading';

    const ISSUE_LABEL = 'Headings without content';


    /**
     * Get the pages with anchor issues.
     *
     * This function runs an SQL query to find pages with anchor tags that have a # destination.
     * It checks if there is an element with a matching ID for each anchor.
     * If there is no matching element, it adds the page to the $this->pages_with_issue property.
     *
     * @return array An array of pages with empty heading issues.
     */
    public function getPagesWithIssues(): array
    {
        $pages_with_issue = [];

        // Run an SQL query to find pages with tables that have anchor tags with a `#...` destination.
        global $wpdb;

        $query = "
            SELECT ID, post_content, post_modified
            FROM {$wpdb->posts}
            WHERE post_type = 'page' AND post_content LIKE '%wp-block-heading%'
        ";

        $wpdb->get_results($query);

        foreach ($wpdb->get_results($query) as $page) :
            $empty_heading_count = $this->getEmptyHeadingsFromContent($page->post_content);

            if ($empty_heading_count === 0) {
                continue;
            }

            $pages_with_issue[$page->ID] = (object)[
                'ID' => $page->ID,
                'empty_heading_count' => $empty_heading_count,
            ];
        endforeach;

        return $pages_with_issue;
    }


    /**
     * Append issues for a specific page.
     *
     * This function checks if the page has header issues and appends them to the issues array.
     *
     * @param array $issues The current issues array.
     * @param int $post_id The ID of the post to check.
     * @return array The issues array with the header issue appended.
     */
    public function appendPageIssues($issues, $post_id)
    {
        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        if (empty($this->pages_with_issue[$post_id])) {
            return $issues;
        }

        $count = $this->pages_with_issue[$post_id]->empty_heading_count;

        $issues[] =  sprintf(_n('There is %d heading without content', 'There are %d headings without content', $count, 'justice'), $count);

        return $issues;
    }

    /**
     * Get all empty headings from the content.
     *
     * This function checks the content for empty heading tags (h1 - h6) and returns the count of empty headings.
     *
     * @param string $content The content to check.
     * @return int The number of empty headings found in the content.
     */
    public static function getEmptyHeadingsFromContent(string $content): int
    {
        if (empty($content)) {
            return 0;
        }

        // Create a new DOMDocument instance to parse the HTML content.
        $dom = new \DOMDocument();
        // Suppress warnings from invalid HTML, this is necessary because the content may not be well-formed HTML.
        // The LIBXML_HTML_NOIMPLIED and LIBXML_HTML_NODEFDTD options prevent the addition of <html> and <body> tags.
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Get all headings, h1 - h6
        $headings = array_merge(
            iterator_to_array($dom->getElementsByTagName('h1')),
            iterator_to_array($dom->getElementsByTagName('h2')),
            iterator_to_array($dom->getElementsByTagName('h3')),
            iterator_to_array($dom->getElementsByTagName('h4')),
            iterator_to_array($dom->getElementsByTagName('h5')),
            iterator_to_array($dom->getElementsByTagName('h6'))
        );

        $empty_headings = [];

        foreach ($headings as $heading) {
            // Check if the heading is empty.
            if (trim($heading->textContent) === '') {
                $empty_headings[] = $heading;
            }
        }

        // Return the count of empty headings.
        return count($empty_headings);
    }
}
