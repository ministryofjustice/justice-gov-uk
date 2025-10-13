<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/Pagination',
    'stories' => []
];

$component['stories']['Default'] = [
    'previous_url' => '#',
    'next_url' => '#',
    'pages' => [
        [
            'title' => '1',
            'url' => '#',
            'current' => true,
        ],
        [
            'title' => '2',
            'url' => '#',
        ],
        [
            'title' => '3',
            'url' => '#',
        ],
    ]
];

return $component;
