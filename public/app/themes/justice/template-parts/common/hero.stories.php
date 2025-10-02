<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/Hero',
    'stories' => []
];

$component['stories']['Default'] = [
    'title' => 'Family Procedure Rules',
    'breadcrumbs' => [
        [
            'label' => 'Home',
            'url' => '/home'
        ],
        [
            'label' => 'Procedure rules',
            'url' => '/procedure-rules'
        ],
        [
            'label' => 'Family Procedure Rules',
            'url' => '/family-procedure-rules'
        ]
    ]
];

$component['stories']['WithEyebrow'] = [
    ...$component['stories']['Default'],
    'eyebrow_text' => 'Procedure rules'
];

return $component;
