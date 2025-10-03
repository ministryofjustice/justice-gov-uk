<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/NavigationSecondary',
    'layout' => 'sidebar',
    'stories' => []
];

$component['stories']['Default'] = [
    'id' => 'navigation-secondary-default',
    'title' => 'Navigation Secondary',
    'links' => [
        [
            'url' => '#',
            'label' => 'Link 1',
            'new_tab' => false,
        ],
        [
            'url' => '#',
            'label' => 'Link 2',
            'new_tab' => true,
        ],
        [
            'url' => '#',
            'label' => 'Link 3',
            'new_tab' => false,
        ],
    ],
];

return $component;
