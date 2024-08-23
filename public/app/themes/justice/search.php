<?php
/**
 * The template for displaying the search page
 *
 */

use MOJ\Justice\Search;
use MOJ\Justice\Taxonomies;
use Timber\PostQuery;

$search = new Search();
$taxonomies = (new Taxonomies())->getTaxonomiesForFilter();


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
get_footer();

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

$results = [];

$searchBarBlock = [
    'variant' => 'main',
    'results' => $search->getResultCount(),
    'filter' => $search->getSortOptions(),
    'search' => [
        'id' => 'search-bar-main',
        'action' => '/search',
    ]
];

$filterBlock = [
    'variant' => 'search-filter',
    'title' => 'Filters',
    'subtitle' => 'Filter results by',
    'submitText' => 'Apply filters',
    'fields' => $filters,
];

$templates = ['templates/search.html.twig'];

$context = Timber::context([
    'title' => 'Search',
    'breadcrumbs' => $breadcrumbs,
    'filter' => $filterBlock,
    'searchBlock' => $searchBarBlock,
    'results' => $results,
]);
Timber::render($templates, $context);
