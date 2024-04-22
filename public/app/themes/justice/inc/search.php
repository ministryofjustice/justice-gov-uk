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
        // Add a rewrite rule to handle the search page.
        add_action('init',  fn () => add_rewrite_rule('search[/]?$', 'index.php', 'top'));
        add_action('pre_get_posts', [$this, 'includeDocumentsInSearchResults']);
        add_filter('the_excerpt', [$this, 'highlightResults']);
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

    public function getResultCount()
    {
        global $wp_query;
        return $wp_query->found_posts;
    }

    public function highlightResults($text)
    {
        if (is_search()) {
            $sr = get_query_var('s');
            $keys = explode(" ", $sr);
            $text = preg_replace('/(' . implode('|', $keys) . ')/iu', '<strong class="search-excerpt">' . $sr . '</strong>', $text);
        }
        return $text;
    }

    public function formattedUrl($url)
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

    public function getSortOptions ()
    {
        $orderby = get_query_var('orderby');
        return [
            'relevance' => [
                'label' => 'Relevance',
                'url' => '/search/?s=' . get_query_var('s'),
                'selected' => empty($orderby) || $orderby === 'relevance',
            ],
            'date' => [
                'label' => 'Most recent',
                'url' => '/search/?s=' . get_query_var('s') . '&orderby=date',
                'selected' => $orderby === 'date',
            ],
        ];
    }


}
