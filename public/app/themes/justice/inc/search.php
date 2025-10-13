<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class Search
{
    public function addHooks()
    {
        // Add a rewrite rule to handle an empty search.
        add_action('init', fn() => add_rewrite_rule('search/?$', 'index.php?s=', 'top'));
        // Add a rewrite rule to handle the old search urls.
        add_action('template_redirect', [$this, 'redirectOldSearchUrls']);
        // Add a rewrite rule to handle the search string.
        add_filter('posts_search', [$this, 'handleEmptySearch'], 10, 2);
        // Add a query var for the parent page. This will be handled in relevanssiParentFilter.
        add_filter('query_vars', fn($qv) =>  array_merge($qv, array('parent')));
        // Update the search query.
        add_action('pre_get_posts', [$this, 'searchFilter']);

        // Relevanssi - prevent sending documents to the Relevanssi API.
        add_filter('option_relevanssi_do_not_call_home', fn() => 'on');
        add_filter('default_option_relevanssi_do_not_call_home', fn() => 'on');

        // Relevanssi - prevent click tracking. We don't need it and it makes the search results url messy.
        add_filter('option_relevanssi_click_tracking', fn() => 'off');
        add_filter('default_option_relevanssi_click_tracking', fn() => 'off');

        // Relevanssi - filters the did you mean url, to use /search instead of s=.
        add_filter('relevanssi_didyoumean_url', [$this, 'didYouMeanUrl'], 10, 3);
        // Relevanssi - add numbers to the did you mean alphabet.
        add_filter('relevanssi_didyoumean_alphabet', fn($alphabet) => $alphabet . '0123456789');

        // Relevanssi - filters the search results to only include the descendants.
        add_filter('relevanssi_hits_filter', [$this, 'relevanssiParentFilter']);

        // Relevanssi - remove columns for non-admins.
        add_filter('manage_page_posts_columns', [$this, 'removeColumns']);
        add_filter('manage_document_posts_columns', [$this, 'removeColumns']);

        // Relevanssi - remove searches submenus for non-admins.
        add_filter('admin_menu', [$this, 'removeSearchesSubMenus'], 999);

        // Relevanssi - fix duplicates due to revisions.
        // https://www.relevanssi.com/knowledge-base/publishpress-revisions-duplicate-posts/
        add_action('revision_applied', fn($published, $revision) => relevanssi_remove_doc($revision->ID), 10, 2);

        // Redirect the user to the search page if the URI contains multiple pages.
        add_action('init', [$this, 'redirectMultiplePageInURI'], 1);

        // Redirect the user to the search page if there are arrays in the the query string.
        add_action('init', [$this, 'redirectIfQueryStringHasArrays'], 1);
    }


    public static function getSearchPageTitle(): string
    {
        $query = get_search_query();

        $parent_id = get_post((int) get_query_var('parent')) ? (int) get_query_var('parent') : null;
        $parent_title = $parent_id ? get_the_title($parent_id) : null;

        // Set the title based on whether there is a search query.
        $title = $query ? 'Search Results' : 'Search';

        // If there is a parent title, append it to the title.
        if ($parent_title) {
            // e.g. "Search in Civil Procedure Rules"
            // or,  "Search Results for Civil Procedure Rules"
            $title .= $query ? " for " : " in ";
            $title .= $parent_title;
        }

        return $title;
    }


    public static function getSearchFormLabel(): string
    {
        // Are we on the search page?
        if (is_search()) {
            $parent_id = get_post((int) get_query_var('parent')) ? (int) get_query_var('parent') : null;
            $parent_title = $parent_id ? get_the_title($parent_id) : null;
            return $parent_title ? "Enter your $parent_title search" : 'Search';
        }

        // Are we on a page that has a search block?
        if (is_page() || is_single()) {
            return sprintf("Enter your %s search", get_the_title());
        }

        return 'Search';
    }


    /**
     * Check if the search query is empty.
     *
     * @return bool True if the search query is empty, false otherwise.
     */
    public static function hasEmptyQuery(): bool
    {
        return empty(get_search_query());
    }

    /**
     * Get the number of search results.
     *
     * @return int|null The number of search results.
     */
    public static function getResultCount(): ?int
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
    public static function getSearchUrl($search, $args = [])
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
    public static function getSortOptions(): array
    {
        $orderby = get_query_var('orderby');

        return [
            'relevance' => [
                'label' => 'Relevance',
                'url' =>  self::getSearchUrl(get_query_var('s'), ['orderby' => null]),
                'selected' => empty($orderby) || $orderby === 'relevance',
            ],
            'date' => [
                'label' => 'Most recent',
                'url' => self::getSearchUrl(get_query_var('s'), ['orderby' => 'date']),
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

        if (is_null($search)) {
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
     * Get did you mean
     *
     * This function uses Relevanssi's premium feature to generate a suggestion for the search term.
     * If no suggestion is found, it returns true.
     *
     * @return array|true An array containing the suggestion URL and term,
     *   or true if the search term is correct, or false if no suggestion is found.
     */
    public static function getDidYouMean(): array|bool
    {
        $suggestion = relevanssi_premium_generate_suggestion(get_query_var('s'));

        if (true === $suggestion) {
            // Search term is correct, no suggestion needed.
            return true;
        }

        if ('' === $suggestion) {
            // No suggestion found.
            return false;
        }

        return [
            'url' => self::getSearchUrl($suggestion),
            'term' => $suggestion,
        ];
    }


    /**
     * Get form values for the search form.
     *
     * This function returns an array of form values to be used in the search form or filter.
     * It is useful for getting values for hidden inputs.
     *
     * @param array $exclude An array of form fields to exclude from the returned values.
     * @return array An array of form values, each with a name and value.
     */
    public static function getFormValues(array $exclude = []): array
    {
        $fields = [
            // Search term.
            's' => true,
            // Miscellaneous fields.
            'orderby' => true,
            'parent' => true,
            'post_types' => true,
            // Taxonomies.
            'audience' => true,
            'organisation' => true,
            'section' => true,
            'type' => true,
        ];

        foreach ($exclude as $field) {
            if (in_array($field, $fields)) {
                $fields[$field] = false;
            }
        }

        $return_array = [];

        if ($fields['s']) {
            $return_array[] = ['name' => 's', 'value' => get_search_query()];
        }

        if ($fields['orderby'] && in_array(get_query_var('orderby'), ['date', 'relevance'])) {
            $return_array[] = ['name' => 'orderby', 'value' => get_query_var('orderby')];
        }

        if ($fields['parent']) {
            $parent_id = get_post((int) get_query_var('parent')) ? (int) get_query_var('parent') : null;
            if ($parent_id) {
                $return_array[] = [
                    'name' => 'parent',
                    'value' => $parent_id,
                ];
            }
        }

        if ($fields['post_types'] && get_query_var('post_types') === 'page') {
            $return_array[] = [
                'name' => 'post_types',
                'value' => 'page',
            ];
        }

        if ($fields['audience'] && term_exists(get_query_var('audience'), 'audience')) {
            $return_array[] = [
                'name' => 'audience',
                'value' => get_query_var('audience'),
            ];
        }

        if ($fields['organisation'] && term_exists(get_query_var('organisation'), 'organisation')) {
            $return_array[] = [
                'name' => 'organisation',
                'value' => get_query_var('organisation'),
            ];
        }

        if ($fields['section'] && term_exists(get_query_var('section'), 'section')) {
            $return_array[] = [
                'name' => 'section',
                'value' => get_query_var('section'),
            ];
        }

        if ($fields['type'] && term_exists(get_query_var('type'), 'type')) {
            $return_array[] = [
                'name' => 'type',
                'value' => get_query_var('type'),
            ];
        }

        return $return_array;
    }


    /**
     * Get the pagination arguments for the search results.
     *
     * @return array An array containing the previous URL, next URL, and pages.
     */
    public static function getPaginationArgs(): array
    {

        if (is_single()) {
            return [];
        }

        global $paged;
        global $wp_query;

        // Is there a search query?
        if (empty(get_search_query())) {
            // No search query, no pagination.
            return [];
        }

        // Make the pages array
        $pages = [];
        for ($i = 1; $i <= $wp_query->max_num_pages; $i++) {
            $pages[] = [
                'url' => get_pagenum_link($i),
                'title' => $i,
                'current' => $i === (int) $paged,
            ];
        }

        return [
            'previous_url' => (int) $paged > 1 ? get_previous_posts_page_link() : null,
            'next_url' => get_next_posts_page_link($wp_query->max_num_pages),
            'pages' => $pages
        ];
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

    /**
     * Handle malformed search URLs where the path has multiple pages.
     *
     * e.g /search/the/page/page/11
     *
     * @return void
     */
    public function redirectMultiplePageInURI(): void
    {
        // Trim the first and last slash
        $uri = trim($_SERVER['REQUEST_URI'], '/');
        // Split the URI by '/'
        $uri_parts = explode('/', $uri);

        // Check if the URI has at least 4 parts and the first part is 'search'
        if (sizeof($uri_parts) < 4 || $uri_parts[0] !== 'search') {
            return;
        }

        // Remove the first 2 from the array
        $uri_parts = array_slice($uri_parts, 2);

        // Count the number of times 'page' appears in the $uri_parts
        $pages_count = array_count_values($uri_parts)['page'];

        if ($pages_count > 1) {
            // Redirect to the search page
            $url = home_url('/search');
            wp_redirect($url);
            exit;
        }
    }


    /**
     * Handle malformed search URLs with arrays in the query string.
     *
     * This function will redirect the user to the search page if the query string contains arrays.
     * e.g. /search?audience[$testing]=1 or /search?audience%5B%24testing%5D=1
     *
     * @return void
     */
    public function redirectIfQueryStringHasArrays(): void
    {
        // Are we on a search page? The URI starts with /search
        if (strpos($_SERVER['REQUEST_URI'], '/search') === false) {
            return;
        }

        $query_string = explode('&', $_SERVER['QUERY_STRING'] ?? '');

        foreach ($query_string as $query) {
            // Get key and value
            [$key] = explode('=', $query);

            // Use regex to see if the key contains any of the invalid strings
            if (preg_match('/(%5B|%5D|\[|\])/', $key)) {
                // Redirect to the search page
                $url = home_url('/search');
                wp_redirect($url);
                exit;
            }
        }
    }
}
