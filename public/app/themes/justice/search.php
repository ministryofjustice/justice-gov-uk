<?php
/**
 * The template for displaying the search page
 *
 */

use MOJ\Justice\Search;
use MOJ\Justice\Taxonomies;

global $wp_query;

$search = new Search();
$taxonomies = (new Taxonomies())->getTaxonomiesForFilter();
$document = new WP_Document_Revisions;

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

$query = get_search_query();
$results = Timber::get_posts($wp_query);

$formattedResults = [];

foreach ($results as $result) {
    $postId = $result->id;
    $filesize = null;
    $format = null;
    $url = $search->formattedUrl(get_the_permalink($postId));


    if ($document->verify_post_type($postId)) {
        $upload_dir = $document->document_upload_dir();
        $year_month = str_replace('-', '/', substr($result->post_date, 0, 7));
        $format = $document->get_file_type($postId);
        $document_url = "{$upload_dir}/{$year_month}/{$result->post_title}{$format}";
        if (file_exists($document_url)) {
            $filesize = size_format(wp_filesize($document_url));
        }
        if ($format) {
            $format = strtoupper(ltrim($format, '.'));
        }
    }
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
$suggestedTerm = relevanssi_premium_generate_suggestion($query);
$didYouMean = [
    'term' => $suggestedTerm,
    'url' => `search/${suggestedTerm}`
];

$searchBarBlock = [
    'variant' => 'main',
    'results' => $search->getResultCount(),
    'filters' => $search->getSortOptions(),
    'didYouMean' => $didYouMean,
    'search' => [
        'id' => 'search-bar-main',
        'action' => '/search',
        'input'=> [
            'labelHidden'=> true,
            'label' => 'Search',
            'id' => 'searchbox-top',
            'name' => 'query',
            'value' => $query,
        ],
        'button' => [
            'text' => 'Search',
        ]
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
    'results' => $formattedResults,
    'searchQuery' => $query,
    'pagination' => $pagination,
]);

Timber::render($templates, $context);
