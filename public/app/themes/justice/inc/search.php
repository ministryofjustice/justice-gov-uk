<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class Search
{
    public function addHooks()
    {
        add_action('init', function () {
            // Add a rewrite rule to handle an empty search.
            add_rewrite_rule('search/?$', 'index.php?s=', 'bottom');

            // Use the search.php file on /search so that it doesn't 404
            $requestUri =  ltrim($_SERVER['REQUEST_URI'], '/');
            if ($requestUri === 'search' || $requestUri === 'search?s=') {
                // Load the template if it exists
                $load = locate_template('search.php', true);
                if ($load) {
                    exit(); // Exit if the template is found, otherwise 404 repeats
                }
            }
        });
        // Add a rewrite rule to handle the old search urls.
        add_action('template_redirect', [$this, 'redirectOldSearchUrls']);
        // Add a rewrite rule to handle the search string.
        add_filter('posts_search', [$this, 'handleEmptySearch'], 10, 2);
        // Add a query var for the parent page. This will be handled in relevanssiParentFilter.
        add_filter('query_vars', fn ($qv) =>  array_merge($qv, array('parent')));
        // Update the search query.
        add_action('pre_get_posts', [$this, 'searchFilter']);

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

        // Relevanssi - remove columns for non-admins.
        add_filter('manage_page_posts_columns', [$this, 'removeColumns']);
        add_filter('manage_document_posts_columns', [$this, 'removeColumns']);

        // Relevanssi - remove searches submenus for non-admins.
        add_filter('admin_menu', [$this, 'removeSearchesSubMenus'], 999);
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
        $pass_through_params = ['parent', 'post_types', 'orderby', 'section', 'organisation', 'type', 'audience'];
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

        // Unslash because wp adds \ before quotes. Then immediately urlencode.
        $encoded_search = urlencode(wp_unslash($search));

        return home_url('/search/' . $encoded_search .  $url_append);
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
     * Update the search query.
     *
     * Setting the default value for paged is important to highlight page 1 in pagination.
     *
     * @param \WP_Query $query The main WordPress query.
     * @return void
     */

    public function searchFilter($query)
    {
        if (!is_admin() && $query->is_main_query() && $query->is_search) {
            $query->set('paged', (get_query_var('paged')) ? get_query_var('paged') : 1);
        }
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

        $parent_id = $wp_query->query_vars['parent'];

        $acc = [];
        foreach ($hits[0] as $hit) {
            // Loop through all the posts found.
            if ($hit->ID === $parent_id) {
                // The page itself.
                $acc[] = $hit;
            } elseif ($hit->post_parent === $parent_id) {
                // A direct descendant.
                $acc[] = $hit;
            } elseif ($hit->post_parent > 0) {
                $ancestors = get_post_ancestors($hit);
                if (in_array(intval($parent_id), $ancestors, true)) {
                    // One of the lower level descendants.
                    $acc[] = $hit;
                }
            }
        }

        // Only include the filtered posts.
        $hits[0] = $acc;
        return $hits;
    }

    /**
     * Remove columns from the admin edit screen e.g. All Pages.
     *
     * For a simpler editing experience, we remove some columns for non-admins.
     *
     * @param array $columns The columns for the admin screen.
     * @return array The columns after removing any un-necessary ones.
     */

    public function removeColumns(array $columns): array
    {
        if (!current_user_can('manage_options')) {
            unset($columns['exclude_post']);
            unset($columns['ignore_content']);
            unset($columns['pinned_keywords']);
            unset($columns['pin_for_all']);
            unset($columns['unpinned_keywords']);
        }

        return $columns;
    }

    /**
     * Remove the User Searches & Admin Searches submenu for non-admins.
     *
     * @return void
     */

    public function removeSearchesSubMenus()
    {
        if (!current_user_can('manage_options')) {
            remove_submenu_page('index.php', 'relevanssi-premium/relevanssi.php');
            remove_submenu_page('index.php', 'relevanssi_admin_search');
        }
    }
}
