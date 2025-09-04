<?php

/**
 * The template for displaying the search page
 *
 */

use MOJ\Justice\Search;
use MOJ\Justice\Taxonomies;
use \MOJ\Justice\Content;

global $wp_query;
$query = get_search_query();

$search = new Search();
$taxonomies = (new Taxonomies())->getTaxonomiesForFilter();
$document = new WP_Document_Revisions;
$contentHelper = new Content();

$formattedResults = [];
$didYouMean = null;
$pagination = null;

$breadcrumbs = [
    [
        'label' => 'Home',
        'url' => '/',
    ],
    [
        'label' => 'Search',
        'url' => '/search',
    ]
];

get_header();

// Get the sort filters
$filters = array_map(function ($taxonomy) {
    $options = [];
    $default = null;
    foreach ($taxonomy->terms as $term) {
        $default = $term->selected ? $term->slug : $default;
        $options[] = [
            'label' => $term->name,
            'value' => $term->slug,
        ];
    }
    return [
        'title' => $taxonomy->label,
        'group' => $taxonomy->name,
        'type' => 'radio',
        'defaults' => $default,
        'direction' => 'vertical',
        'options' => $options,
    ];
}, $taxonomies);

// Add a checkbox to exclude documents
$filters[] = [
    'type' => 'checkbox',
    'defaults' => [get_query_var('post_types') === 'page' ? 'page' : null],
    'group' => 'post_types',
    'options' => [
        [
            'label' => 'Limit your search to web pages only',
            'value' => 'page',
        ]
    ],
];

if ($query) {
    $results = Timber::get_posts($wp_query);

    $formattedResults = [];

    // Format the results into a structure the frontend understands
    foreach ($results as $result) {
        $postId = $result->id;
        $filesize = null;
        $url = get_the_permalink($postId);
        $format = pathinfo($url, PATHINFO_EXTENSION);

        $filesize = $contentHelper->getFormattedFilesize($postId);
        $format = strtoupper(ltrim($format, '.'));

        $formattedResults[] = [
            'title' => $result->post_title,
            'url' => $url,
            'date' => get_the_date('j F Y', $result),
            'description' => $result->post_excerpt,
            'isDocument' => $result->post_type === 'document',
            'filesize' => $filesize ?: null,
            'format' => $format,
        ];
    }
    $pagination = $results->pagination();
    $didYouMean = [];
    $suggestedTerm = relevanssi_premium_generate_suggestion($query);
    // If the term used is valid relevanssi_premium_generate_suggestion returns true
    // We don't need to display anything if this is the case
    if ($suggestedTerm !== true) {
        $didYouMean = [
            'term' => $suggestedTerm,
            'url' => $search->getSearchUrl($suggestedTerm),
        ];
    }
}

$allowedParams = ['parent', 'post_types', 'orderby', 'section', 'organisation', 'type', 'audience'];
// Get current query params to persist across searches
$hiddenInputs = array_filter(array_map(function ($input) {
    $value = get_query_var($input);
    if ($value) {
        return [
            'name' => $input,
            'value' => $value,
        ];
    }
    return null;
}, $allowedParams));

$parentId = get_query_var('parent');
$parentTitle = $parentId ? get_the_title($parentId) : null;

$searchBarBlock = [
    'variant' => 'main',
    'results' => $search->getResultCount(),
    'filters' => $search->getSortOptions(),
    'didYouMean' => $didYouMean,
    'search' => [
        'id' => 'search-bar-main',
        'action' => '/search',
        'input' => [
            'labelHidden' => true,
            'label' => $parentTitle ? "Enter your $parentTitle search" : 'Search',
            'id' => 'searchbox-top',
            'name' => 's',
            'value' => $query,
        ],
        'hiddenInputs' => $hiddenInputs,
        'button' => [
            'text' => 'Search',
        ]
    ]
];

$sortOrder = get_query_var('orderby');

$filterBlock = [
    'variant' => 'search-filter',
    'title' => 'Filters',
    'subtitle' => 'Filter results by',
    'submitText' => 'Apply filters',
    'noQuery' => !$query,
    'hiddenInputs' => [
        [
            'name' => 's',
            'value' => $query,
        ],
        ...($sortOrder ? [[
            'name' => 'orderby',
            'value' => $sortOrder,
        ]] : []),
        ...($parentId ? [[
            'name' => 'parent',
            'value' => $parentId,
        ]] : []),
    ],
    'fields' => $filters,
];

$templates = ['templates/search.html.twig'];

// Set the title based on whether there is a search query.
$title = $query ? 'Search Results' : 'Search';

// If there is a parent title, append it to the title.
if ($parentTitle) {
    // e.g. "Search in Civil Procedure Rules"
    // or,  "Search Results for Civil Procedure Rules"
    $title .= $query ? " for " : " in ";
    $title .= $parentTitle;
}

$context = Timber::context([
    'title' => $title,
    'breadcrumbs' => $breadcrumbs,
    'filter' => $filterBlock,
    'searchBlock' => $searchBarBlock,
    'results' => $formattedResults,
    'searchQuery' => $query,
    'pagination' => $pagination,
]);

Timber::render($templates, $context);

get_footer();
