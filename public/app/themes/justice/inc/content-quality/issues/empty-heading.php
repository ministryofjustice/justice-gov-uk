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
            SELECT 
                ID,
                COALESCE(options.option_value, 'queued') AS issue_count
            FROM {$wpdb->posts} AS p
            -- To save us from running get_transient in a php loop, 
            -- we can join the options table to get the transient value here
            LEFT JOIN {$wpdb->options} AS options 
            ON options.option_name = CONCAT('_transient_moj:content-quality:issue:empty-heading:', p.ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta
            ON postmeta.post_id = ID AND postmeta.meta_key = '_content_quality_exclude'
            WHERE
                -- options value should be null or not an empty serialized array
                ( options.option_value IS NULL OR options.option_value != 0 ) AND
                post_type = 'page' 
                AND post_content LIKE '%wp-block-heading%'
                -- Post status should be publish, private or draft
                AND post_status IN ('publish', 'private', 'draft')
                -- If the _content_quality_exclude meta key is not set, or is set to 0.
                AND (postmeta.meta_value IS NULL OR postmeta.meta_value = '0')
        ";

        // Loop over every page, and unserialize the value of this page's spelling issues.
        foreach ($wpdb->get_results($query) as $page) :
            $pages_with_issue[$page->ID] = $page->issue_count;
        endforeach;

        return $pages_with_issue;
    }



    public function processPages(): void
    {
        $transient_updates = [];

        // Run an SQL query to find pages with tables that have anchor tags with a `#...` destination.
        global $wpdb;

        $query = "
            SELECT 
                ID,
                p.post_content
            FROM {$wpdb->posts} AS p
            -- To save us from running get_transient in a php loop, 
            -- we can join the options table to get the transient value here
            LEFT JOIN {$wpdb->options} AS options 
            ON options.option_name = CONCAT('_transient_moj:content-quality:issue:empty-heading:', p.ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta_2
            ON postmeta_2.post_id = ID AND postmeta_2.meta_key = '_content_quality_exclude'
            WHERE
                -- options value should be null
                options.option_value IS NULL AND
                -- Post type should be page 
                post_type = 'page' 
                AND post_content LIKE '%wp-block-heading%'
                -- Post status should be publish, private or draft
                AND post_status IN ('publish', 'private', 'draft')
                -- Exclude pages that have the _content_quality_exclude meta key set to 1
                AND (postmeta_2.meta_value IS NULL OR postmeta_2.meta_value = '0')
        ";

        foreach ($wpdb->get_results($query) as $page) :
            // Add the value to the transient updates array, this will be used in a bulk update later.
            $transient_updates["$this->transient_key:{$page->ID}"] = $this->getEmptyHeadingsFromContent($page->post_content);
        endforeach;

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

        // If the issue is 'queued', then append the appropriate message.
        if ('queued' === $this->pages_with_issue[$post_id]) {
            $issues[] = __('The page is queued for heading without content issues.', 'justice');
            return $issues;
        }

        $count = $this->pages_with_issue[$post_id];

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
