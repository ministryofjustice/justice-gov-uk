<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/SearchBarBlock',
    'stories' => []
];

$component['stories']['Default'] = [
    'result_count' => 42,
    'search_form' => [
        'id' => 'search-bar-main',
        'action' => '/search',
        'input' => [
            'label_hidden' => true,
            'label' => 'Search',
            'id' => 'search-main-input',
            'name' => 's',
            'value' => '',
        ],
        'button' => [
            'text' => 'Search'
        ]
    ],
    'did_you_mean' => [
        'term' => 'alternative term',
        'url' => '/search?query=alternative+term'
    ],
    'filters' => [
        [
            'label' => 'Relevance',
            'selected' => true,
            'url' => '/search?sort=relevant'
        ],
        [
            'label' => 'Date',
            'selected' => false,
            'url' => '/search?sort=date'
        ]
    ]
];

return $component;
