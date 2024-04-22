<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

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
        add_action('init', [$this, 'redirectOldSearchUrls']);
        add_filter('posts_search', [$this, 'handleEmptySearch'], 10, 2);
        add_action('pre_get_posts', [$this, 'includeDocumentsInSearchResults']);
        add_action('pre_get_posts', [$this, 'searchFilter']);
        add_filter('the_excerpt', [$this, 'highlightResults']);

        add_filter('relevanssi_didyoumean_alphabet', function ($alphabet) {
            return $alphabet . '0123456789';
        });
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
                'url' => '/search/' . get_query_var('s'),
                'selected' => empty($orderby) || $orderby === 'relevance',
            ],
            'date' => [
                'label' => 'Most recent',
                'url' => '/search/' . get_query_var('s') . '?orderby=date',
                'selected' => $orderby === 'date',
            ],
        ];
    }

    public function getSuggestion(): ?string
    {
        if (empty(get_search_query())) {
            return null;
        }
        return null;
        // $q = get_search_query(false);
        // error_log(print_r('$q: ' . $q, true));
        // $s = \relevanssi_didyoumean($q, '', '', 5);
        // error_log(print_r($s, true));
        // return 'x';
    }

    /**
     * Redirect old search URLs to the new search page.
     *
     * @return void
     */

    public function redirectOldSearchUrls()
    {

        $search_params = ['s' => null];

        if (isset($_GET['s'])) {
            // Redirect the s parameter to the new search page.
            $search_params['s'] = $_GET['s'];
        } else if (isset($_GET['query'])) {
            // Redirect old search URLs to the new search page.
            $search_params['s'] = $_GET['query'];
        }

        if (!$search_params['s']) {
            return;
        }

        wp_redirect('/search/' . $search_params['s']);
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

    // TODO: Redirect old query string search URLs to the new search page.

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

    public function searchFilter($query)
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
     * @param string $text The text to highlight.
     * @return string The highlighted text.
     */

    public function highlightResults($text)
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
}
