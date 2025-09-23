<?php

if (!defined('ABSPATH')) {
    exit;
}
use Roots\WPConfig\Config;
use MOJ\Justice\Taxonomies;

if (Config::get('FRONTEND_VERSION') === 1) {
    require get_template_directory() . '/sidebar.v1.php';
    return;
}

use MOJ\Justice\PostMeta;

$post_meta = new PostMeta(\get_the_ID(), $args);

if ($post_meta->hasPanel('menu')) {
    get_template_part('template-parts/panels/menu');
}

if ($post_meta->hasPanel('search-filters')) {
    get_template_part('template-parts/panels/search-filters', null, [
        'fields' => [
            ...Taxonomies::getTaxonomiesForFilter(),
            [
                'group'=> 'post_types',
                'type' => 'checkbox',
                'options' => [
                    [
                        'value' => 'page',
                        'label' => 'Limit your search to web pages only',
                        'checked' => get_query_var('post_types') === 'page',
                    ],
                ],
            ]
        ],
    ]);
}
