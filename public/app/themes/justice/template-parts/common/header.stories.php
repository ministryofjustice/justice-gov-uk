<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/Header',
    'stories' => []
];

// Convert the above to php
$component['stories']['Default'] = [
    'show_logo' => true,
    'show_search' => true,
    'links' => [
        [
            'url' => '#',
            'label' => 'Courts',
        ],
        [
            'url' => '#',
            'label' => 'Procedure rules',
        ],
        [
            'url' => '#',
            'label' => 'Offenders',
        ]
    ],
    'search_form' => [
        'id' => 'search-bar-header',
        'action' => '#',
        'input' => [
            'id' => 'search-bar-header-input',
            'name' => 's',
            'label' => 'Search the Justice UK website',
            'label_hidden' => true,
        ],
        'button' => [
            'text'  => 'Search',
        ]
    ]
];


$component['stories']['ActiveLink'] = [
    ...$component['stories']['Default'],
    'links' => [
        [
            'url' => '#',
            'label' => 'Courts',
        ],
        [
            'url' => '#',
            'label' => 'Procedure rules',
            'active' => true, // This link is active
        ],
        [
            'url' => '#',
            'label' => 'Offenders',
        ]
    ]
];

return $component;
