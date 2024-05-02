<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class Search
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks()
    {
        // Add a rewrite rule to handle an empty search.
        add_action('init', fn () => add_rewrite_rule('search/?$', 'index.php?s=', 'bottom'));
        // Add a rewrite rule to handle the old search urls.
        add_action('template_redirect', [$this, 'redirectOldSearchUrls']);
        // Add a rewrite rule to handle the search string.
        add_filter('posts_search', [$this, 'handleEmptySearch'], 10, 2);
        // Add a query var for the parent page. This will be handled in relevanssiParentFilter.
        add_filter('query_vars', fn ($qv) =>  array_merge($qv, array('parent')));

        // Not necessary with Relevanssi.
        // add_action('pre_get_posts', [$this, 'includeDocumentsInSearchResults']);
        // add_action('pre_get_posts', [$this, 'searchFilter']);
        // add_filter('the_excerpt', [$this, 'highlightResults']);

        // Relevanssi - prevent sending documents to the Relevanssi API.
        add_filter('option_relevanssi_do_not_call_home', fn () => 'on');
        add_filter('default_option_relevanssi_do_not_call_home', fn () => 'on');

        // Relevanssi - prevent click tracking. We don't need it and it makes the search results url messy.
        add_filter('option_relevanssi_click_tracking', fn () => 'off');
        add_filter('default_option_relevanssi_click_tracking', fn () => 'off');

        // Relevanssi - filters the did you mean url, to use /search instead of s=.
        add_filter('relevanssi_didyoumean_url', [$this, 'didYouMeanUrl'], 10, 3);
        // Relevanssi - add numbers to the did you mean alphabet.
        add_filter('relevanssi_didyoumean_alphabet', fn ($alphabet) => $alphabet . '0123456789');

        // Relevanssi - filters the search results to only include the descendants.
        add_filter('relevanssi_hits_filter', [$this, 'relevanssiParentFilter']);
    }

    /**
     * Check if the search query is empty.
     *
     * @return bool True if the search query is empty, false otherwise.
     */

    public function hasEmptyQuery(): bool
    {
        return empty(get_search_query());
    }

    /**
     * Get the number of search results.
     *
     * @return int|null The number of search results.
     */

    public function getResultCount(): ?int
    {
        if (empty(get_search_query())) {
            return null;
        }

        global $wp_query;
        return $wp_query->found_posts;
    }

    /**
     * Get the URL for the search results.
     *
     * @param string $search The search query.
     * @param array $args An array of query parameters to add or modify.
     * @return string The URL for the search results.
     */

    public function getSearchUrl($search, $args = [])
    {
        $url_append = '';
        $pass_through_params = ['parent', 'orderby', 'section', 'organisation', 'type', 'audience'];
        $query_array = [];

        foreach ($pass_through_params as $param) {
            $value = get_query_var($param);
            if (!empty($value)) {
                $query_array[$param] = $value;
            }
        }

        foreach ($args as $key => $value) {
            if ($value === null) {
                unset($query_array[$key]);
                continue;
            } else {
                $query_array[$key] = $value;
            }
        }

        if (!empty($query_array)) {
            $url_append = '?' . http_build_query($query_array);
        }

        return home_url('/search/' . $search .  $url_append);
    }

    /**
     * Get the sort options for the search results.
     *
     * @return array An array of sort options.
     */

    public function getSortOptions(): array
    {
        $orderby = get_query_var('orderby');

        return [
            'relevance' => [
                'label' => 'Relevance',
                'url' =>  $this->getSearchUrl(get_query_var('s'), ['orderby' => null]),
                'selected' => empty($orderby) || $orderby === 'relevance',
            ],
            'date' => [
                'label' => 'Most recent',
                'url' => $this->getSearchUrl(get_query_var('s'), ['orderby' => 'date']),
                'selected' => $orderby === 'date',
            ],
        ];
    }

    /**
     * Redirect old search URLs to the new search page.
     *
     * @return void
     */

    public function redirectOldSearchUrls()
    {
        // Don't redirect if we're in the admin.
        if (is_admin()) {
            return;
        }

        $search = null;

        if (isset($_GET['s'])) {
            // Redirect the s parameter to the new search page.
            $search = $_GET['s'];
        } else if (isset($_GET['query'])) {
            // Redirect old search URLs to the new search page.
            $search = $_GET['query'];
        }

        if (!$search) {
            return;
        }

        wp_redirect($this->getSearchUrl($search));
        exit;
    }

    /**
     * Handle a search query with no search term.
     *
     * @param string $search The search query.
     * @param \WP_Query $q The main WordPress query.
     * @return string The modified search query.
     */

    public function handleEmptySearch($search, \WP_Query $q)
    {
        if (!is_admin() && empty($search) && $q->is_search() && $q->is_main_query()) {
            // Return an empty search query to prevent any results from being returned.
            $search .= " AND 0=1 ";
        }

        return $search;
    }

    /**
     * This function modifies the main WordPress query to include an array of
     * post types instead of the default 'post' post type.
     *
     * @param object $query The main WordPress query.
     * @return void
     */

    public function includeDocumentsInSearchResults($query): void
    {
        if ($query->is_main_query() && $query->is_search() && !is_admin()) {
            $query->set('post_type', array('page', 'document'));
        }
    }

    /**
     * Pagination support for non-relevanssi search results.
     *
     * @param object $query The main WordPress query.
     * @return void
     */

    public function searchFilter($query): void
    {
        if (!is_admin() && $query->is_main_query()) {
            if ($query->is_search) {
                $query->set('paged', (get_query_var('paged')) ? get_query_var('paged') : 1);
                $query->set('posts_per_page', 10);
            }
        }
    }

    /**
     * Highlight the search terms in the search results.
     *
     * This is not necessary with Relevanssi.
     *
     * @param string $text The text to highlight.
     * @return string The highlighted text.
     */

    public function highlightResults(string $text): string
    {
        if (is_search()) {
            $sr = get_query_var('s');
            $keys = explode(" ", $sr);
            $text = preg_replace('/(' . implode('|', $keys) . ')/iu', '<strong class="search-excerpt">' . $sr . '</strong>', $text);
        }
        return $text;
    }

    /**
     * Format the URL to display in the search results.
     *
     * @param string $url The URL to format.
     * @return string The formatted URL.
     */

    public function formattedUrl(string $url): string
    {
        return $url;
        $split_length = 80;
        // Remove the protocol from the URL.
        $url = preg_replace('/http\:(s)?\/\//', '', $url);

        if (strlen($url) <= $split_length) {
            return $url;
        }

        // Find the index of the last slash before the split length.
        $slash_index = strrpos(substr($url, 0, $split_length), '/');

        // Add a line break tag before the last slash.
        if ($slash_index) {
            $url = substr_replace($url, '<br>/', $slash_index, 1);
        }

        return $url;
    }

    /**
     * Filter the did you mean URL to use /search instead of s=.
     *
     * @param string $url The URL to filter.
     * @param string $query The search query.
     * @param string $suggestion The suggested search query.
     * @return string The filtered URL.
     */

    public function didYouMeanUrl($url, $query, $suggestion): string
    {
        return empty($suggestion) ? $url : $this->getSearchUrl($suggestion);
    }

    /**
     * Filters the search results to only include the descendants of the parent page.
     *
     * This is useful for returning search results for Civil Procedure Rules (CPR) pages.
     *
     * @see https://www.relevanssi.com/knowledge-base/searching-for-all-descendants-of-a-page/
     *
     * @param array $hits The search results.
     * @return array The filtered search results.
     */

    public function relevanssiParentFilter(array $hits): array
    {
        global $wp_query;

        if (!isset($wp_query->query_vars['parent'])) {
            // No parent parameter set, do nothing.
            return $hits;
        }

        $acc = [];
        foreach ($hits[0] as $hit) {
            // Loop through all the posts found.
            if ($hit->ID === $wp_query->query_vars['parent']) {
                // The page itself.
                $acc[] = $hit;
            } elseif ($hit->post_parent === $wp_query->query_vars['parent']) {
                // A direct descendant.
                $acc[] = $hit;
            } elseif ($hit->post_parent > 0) {
                $ancestors = get_post_ancestors($hit);
                if (in_array(intval($wp_query->query_vars['parent']), $ancestors, true)) {
                    // One of the lower level descendants.
                    $acc[] = $hit;
                }
            }
        }

        // Only include the filtered posts.
        $hits[0] = $acc;
        return $hits;
    }
}
