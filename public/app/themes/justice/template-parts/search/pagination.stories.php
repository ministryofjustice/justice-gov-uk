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

$component['stories']['ManyPages'] = [
    'previous_url' => '#',
    'next_url' => '#',
];

// Use a php loop to add 40 pages.
for ($i = 1; $i <= 40; $i++) {
    $component['stories']['ManyPages']['pages'][] = [
        'title' => (string) $i,
        'url' => '#',
        'current' => $i === 1,
    ];
}

return $component;
