<?php

/**
 * This class is for content quality checks and reports.
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';
require_once 'email-href.php';

final class ContentQualityIssueEmailText extends ContentQualityIssue
{
    const ISSUE_SLUG = 'email-text';

    const ISSUE_LABEL = 'Inaccessible email link text';


    /**
     * Get the pages with inaccessible email text issues.
     *
     * This function runs an SQL query to find pages with mailto strings.
     * It checks if the link text is in an accessible format.
     * The link text should eb an email address, not a generic "Email Us" or similar text.
     *
     * @return array An array of pages with thead issues.
     */
    public function getPagesWithIssues(): array
    {
        $pages_with_issue = [];
        $transient_updates = [];

        global $wpdb;

        $query = "
            SELECT ID, post_content, post_modified, options.option_value AS inaccessible_email_text_counts
            FROM {$wpdb->posts}
            -- To save us from running get_transient in a php loop, 
            -- we can join the options table to get the transient value here
            LEFT JOIN {$wpdb->options} AS options 
            ON options.option_name = CONCAT('_transient_moj:content-quality:issue:email-text:', ID)
            -- Where clauses
            WHERE
                -- options value should be null or not 0
                ( options.option_value IS NULL OR options.option_value != '0' ) AND
                -- Post type should be page 
                post_type = 'page' AND
                -- Post content should contain a table tag
                post_content LIKE '%mailto%'
        ";

        foreach ($wpdb->get_results($query) as $page) :
            $inaccessible_email_text_count = is_null($page->inaccessible_email_text_count) ? null : (int)$page->inaccessible_email_text_count;

            if (is_null($inaccessible_email_text_count)) {
                // The table didn't contain a transient value, so we need to check the content.
                $inaccessible_email_text_count = $this->getInaccessibleEmailLinksFromContent($page->post_content);
                // Add the value to the transient updates array, this will be used in a bulk update later.
                $transient_updates["$this->transient_key:{$page->ID}"] = $inaccessible_email_text_count;
            }

            // If the value is > 0, add it to the pages_with_issue array.
            if ($inaccessible_email_text_count) {
                $pages_with_issue[$page->ID] = $inaccessible_email_text_count;
            }
        endforeach;

        if (sizeof($transient_updates)) {
            $expiry = time() + $this->transient_duration;
            $this->bulkSetTransientInDatabase($transient_updates, $expiry);
        }

        return $pages_with_issue;
    }


    /**
     * Append issues for a specific page.
     *
     * This function checks if the page has issues and appends them to the issues array.
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

        $count = $this->pages_with_issue[$post_id];

        $issues[] =  sprintf(_n('There is %d email link with inaccessible text', 'There are %d email links with inaccessible text', $count, 'justice'), $count);

        return $issues;
    }

    /**
     * Get inaccessibly formatted email links found in the content.
     *
     * This function checks for mailto: links, where the link text does not match the email address.
     *
     * @param string $content The content to check.
     * @return int The number of inaccessibly formatted email links found in the content.
     */
    public static function getInaccessibleEmailLinksFromContent(string $content): int
    {
        if (empty($content)) {
            return 0;
        }

        // Create a new DOMDocument instance to parse the HTML content.
        $dom = new \DOMDocument();

        // Suppress warnings from invalid HTML, this is necessary because the content may not be well-formed HTML.
        // The LIBXML_HTML_NOIMPLIED and LIBXML_HTML_NODEFDTD options prevent the addition of <html> and <body> tags.
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $inaccessible_email_text_count = 0;

        foreach ($dom->getElementsByTagName('a') as $a_element) {
            $href = $a_element->getAttribute('href');

            if (empty($href) || strpos($href, 'mailto:') !== 0) {
                // Skip if href is empty or not a mailto link.
                continue;
            }

            $emails = ContentQualityIssueEmailHref::class::getEmailsFromHref($href);

            $link_text = trim($a_element->textContent);

            if (!str_contains($link_text, '@')) {
                $inaccessible_email_text_count++;
                // If the link text does not contain an '@', it's an inaccessible email text.
                continue;
            }

            // If the href is for a single email, it should match the link text.
            if (sizeof($emails) === 1 && strtolower($emails[0]) !== strtolower($link_text)) {
                $inaccessible_email_text_count++;
            }
        }

        return $inaccessible_email_text_count;
    }
}
