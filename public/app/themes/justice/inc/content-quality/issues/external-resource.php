<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'issue.php';



/**
 * This class is for content quality checks related to external resources.
 *
 * It helps identify where an image has been embedded from an external URL,
 * this is important since these images will be blocked by the server's CSP.
 */
final class ContentQualityIssueExternalResource extends ContentQualityIssue
{
    const ISSUE_SLUG = 'external-resource';

    const ISSUE_LABEL = 'Images from an external source';


    /**
     * Load the pages with external image issues.
     *
     * This function retrieves pages with external image issues from cache only.
     *
     * @return array An array of pages with external image issues.
     */
    public function getPagesWithIssues(): array
    {
        $pages_with_issue = [];

        global $wpdb;

        $query = "
            SELECT 
                ID,
                COALESCE(options.option_value, 'queued') AS issues
            FROM {$wpdb->posts} AS p
            LEFT JOIN {$wpdb->options} AS options 
                ON options.option_name = CONCAT('_transient_moj:content-quality:issue:external-resource:', p.ID)
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
     * Process pages for external image issues.
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
                    WHEN p.post_content LIKE '%<img%' THEN p.post_content 
                    ELSE NULL 
                END AS post_content,
                -- Check if the post content contains an <img> tag.
                IFNULL(p.post_content LIKE '%<img%', 0) AS contains_img_string
            FROM {$wpdb->posts} AS p
            LEFT JOIN {$wpdb->options} AS options 
                ON options.option_name = CONCAT('_transient_moj:content-quality:issue:external-resource:', p.ID)
            LEFT JOIN {$wpdb->postmeta} AS postmeta
                ON postmeta.post_id = ID AND postmeta.meta_key = '_content_quality_exclude'
            WHERE 
                ( options.option_value IS NULL ) AND
                post_type = 'page' AND 
                p.post_status IN ('publish', 'private', 'draft') AND
                (postmeta.meta_value IS NULL OR postmeta.meta_value = 0)
        ";

        foreach ($wpdb->get_results($query) as $page) {
            if ($page->contains_img_string == 0) {
                // If the post content does not contain any <img, set the transient value to an empty array.
                // This will prevent the page from being processed again in the future.
                $transient_updates["$this->transient_key:{$page->ID}"] = serialize([]);
                continue;
            }

            $external_resources = $this->getExternalResourcesFromContent($page->post_content);

            // Add the value to the transient updates array, this will be used in a bulk update later.
            $transient_updates["$this->transient_key:{$page->ID}"] = serialize($external_resources);
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
     * @param array $issues The current issues array.
     * @param int $post_id The ID of the post to check.
     * @return array The issues array with the external resource issues appended.
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
            $issues[] = __('The page is queued for external images issues.', 'justice');
            return $issues;
        }

        $resources = $this->pages_with_issue[$post_id];
        $resources_string = implode(', ', $resources);
        $count = count($resources);

        $issues[] =  sprintf(_n('The following resource will not display correctly: %s', 'The following resources will not display correctly: %s', $count, 'justice'), $resources_string);

        return $issues;
    }

    /**
     * Get external resources from content.
     * 
     * @param string $content The content to check.
     * @return array An array of external URLs found in the content.
     */
    public static function getExternalResourcesFromContent(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        $allowed_hosts = [
            'justice.gov.uk',
            'dev.justice.gov.uk',
            'staging.justice.gov.uk',
            'demo.justice.gov.uk',
            'justice.docker',
        ];

        // Create a new DOMDocument instance to parse the HTML content.
        $dom = new \DOMDocument();

        // Suppress warnings from invalid HTML, this is necessary because the content may not be well-formed HTML.
        // The LIBXML_HTML_NOIMPLIED and LIBXML_HTML_NODEFDTD options prevent the addition of <html> and <body> tags.
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $external_resources = [];

        foreach ($dom->getElementsByTagName('img') as $img) {
            // Get src attribute
            $img_src = $img->getAttribute('src');

            // Parse the URL
            $url_parts = parse_url($img_src);

            if (isset($url_parts['host']) && !in_array($url_parts['host'], $allowed_hosts)) {
                $external_resources[] = esc_attr($img_src);
            }
        }

        return $external_resources;
    }
}
