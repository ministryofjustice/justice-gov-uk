<?php

if (!defined('ABSPATH')) {
    exit;
}

use Roots\WPConfig\Config;

if (Config::get('FRONTEND_VERSION') === 1) {
    require get_template_directory() . '/sidebar.v1.php';
    return;
}

use MOJ\Justice\NavigationSecondary;
use MOJ\Justice\PostMeta;
use MOJ\Justice\Search;
use MOJ\Justice\Taxonomies;

$is_mobile = $args['is_mobile'] ?? false;

$post_meta = new PostMeta(\get_the_ID(), $args);

if ($post_meta->hasPanel('menu')) {
    get_template_part('template-parts/nav/navigation-secondary', null, [
        'title' => 'Justice UK',
        'id' => '#main-page-content',
        'links' => (new NavigationSecondary)->getCurrentPageNavigation(),
    ]);
}

if ($post_meta->hasPanel('search-filters')) {

    $fields = Taxonomies::getTaxonomiesForFilter();
    $fields[] = [
        'group' => 'post_types',
        'type' => 'checkbox',
        'options' => [
            [
                'value' => 'page',
                'label' => 'Limit your search to web pages only',
                'checked' => get_query_var('post_types') === 'page',
            ],
        ],
    ];

    // Get the field names, so that they can be excluded from the hidden inputs.
    $field_names = array_map(fn($field) => $field['group'] ?? '', $fields);

    get_template_part('template-parts/panels/search-filters', null, [
        'fields' => $fields,
        'hidden_inputs' => Search::getFormValues($field_names),
        'is_mobile' => $is_mobile,
    ]);
}
