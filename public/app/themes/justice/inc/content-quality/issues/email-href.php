<?php

/**
 * This class is for content quality checks and reports.
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';

final class ContentQualityIssueEmailHref extends ContentQualityIssue
{
    const ISSUE_SLUG = 'email-href';

    const ISSUE_LABEL = 'Invalid email href';


    /**
     * Get the pages with invalid email href issues.
     *
     * This function runs an SQL query to find pages with mailto strings.
     * It checks if the link href contains email addresses in a valid format.
     * If the href is not a valid email address, it is counted as an issue.
     *
     * @return array An array of pages with email-href issues.
     */
    public function getPagesWithIssues(): array
    {
        $pages_with_issue = [];
        $transient_updates = [];

        global $wpdb;

        $query = "
            SELECT ID, post_content, post_modified, options.option_value AS invalid_email_href_count
            FROM {$wpdb->posts}
            -- To save us from running get_transient in a php loop, 
            -- we can join the options table to get the transient value here
            LEFT JOIN {$wpdb->options} AS options 
            ON options.option_name = CONCAT('_transient_moj:content-quality:issue:email-href:', ID)
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
            $invalid_email_href_count = is_null($page->invalid_email_href_count) ? null : (int)$page->invalid_email_href_count;

            if (is_null($invalid_email_href_count)) {
                // The table didn't contain a transient value, so we need to check the content.
                $invalid_email_href_count = $this->getInvalidEmailsFromContent($page->post_content);
                // Add the value to the transient updates array, this will be used in a bulk update later.
                $transient_updates["$this->transient_key:{$page->ID}"] = $invalid_email_href_count;
            }

            // If the value is > 0, add it to the pages_with_issue array.
            if ($invalid_email_href_count) {
                $pages_with_issue[$page->ID] = $invalid_email_href_count;
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
     * @return array The issues array with the email href issues appended.
     */
    public function appendPageIssues($issues, $post_id)
    {
        // Load the pages with issues - don't run this on construct, as it's an expensive operation.
        $this->loadPagesWithIssues();

        if (empty($this->pages_with_issue[$post_id])) {
            return $issues;
        }

        $count = $this->pages_with_issue[$post_id];

        $issues[] =  sprintf(_n('There is %d email link with an invalid email address', 'There are %d email links with an invalid email address', $count, 'justice'), $count);

        return $issues;
    }

    /**
     * Get inconsistently formatted emails from the content.
     *
     * This function checks for mailto: links for invalid email strings.
     *
     * @param string $content The content to check.
     * @return int The number of invalid email links found in the content.
     */
    public static function getInvalidEmailsFromContent(string $content): int
    {
        if (empty($content)) {
            return 0;
        }

        // Create a new DOMDocument instance to parse the HTML content.
        $dom = new \DOMDocument();

        // Suppress warnings from invalid HTML, this is necessary because the content may not be well-formed HTML.
        // The LIBXML_HTML_NOIMPLIED and LIBXML_HTML_NODEFDTD options prevent the addition of <html> and <body> tags.
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $invalid_email_href_count = 0;

        foreach ($dom->getElementsByTagName('a') as $a_element) {
            $href = $a_element->getAttribute('href');

            if (empty($href) || strpos($href, 'mailto:') !== 0) {
                continue; // Skip if href is empty or not a mailto link.
            }

            $emails = self::getEmailsFromHref($href);

            // If any of the emails is false then we have an invalid email.
            if (in_array(false, $emails, true)) {
                $invalid_email_href_count++;
            }
        }

        return $invalid_email_href_count;
    }

    /**
     * Get emails from a mailto href.
     *
     * This function extracts email addresses from a mailto href string.
     * It validates the email addresses and returns an array of valid emails.
     * If an email is invalid, it returns false for that email.
     * If the href is empty or not a mailto link, it returns an empty array.
     *
     * @param string $href The href string to extract emails from.
     * @return array An array of valid email addresses or false for invalid emails.
     */
    public static function getEmailsFromHref(string $href): array
    {
        if (empty($href) || strpos($href, 'mailto:') !== 0) {
            return []; // Return an empty array if href is empty or not a mailto link.
        }

        // Extract the email address, it's the characters after mailto:, and before ?
        $email_string = preg_replace('/^mailto:(.*?)(\?.*)?$/', '$1', $href);

        $email_array = explode(',', $email_string);

        $trimmed = array_map('trim', $email_array);

        $validated = array_map(function ($email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email; // Return the email if it's valid.
            }

            // Percent decode the email address.
            $decoded_email = trim(rawurldecode($email));

            if (filter_var($decoded_email, FILTER_VALIDATE_EMAIL)) {
                // Return the email if it's valid.
                return $decoded_email;
            }

            // Return false for invalid emails.
            return false;
        }, $trimmed);

        return $validated;
    }
}
