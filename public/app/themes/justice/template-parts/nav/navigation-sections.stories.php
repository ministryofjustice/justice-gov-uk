<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/NavigationSection',
    'layout' => 'sidebar',
    'stories' => []
];

$component['stories']['Default'] = [
    'links' => [
        [
            'url' => '#',
            'label' => '1-10'
        ],
        [
            'url' => '#',
            'label' => '11-20'
        ],
        [
            'url' => '#',
            'label' => '21-30'
        ],
        [
            'url' => '#',
            'label' => '31-40'
        ],
        [
            'url' => '#',
            'label' => '41-50'
        ]
    ],
];

return $component;
