<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/Footer',
    'parameters' => [
        'layout' => 'fullscreen',
    ],
    'stories' => []
];

$component['stories']['Default'] = [
    'links' => [
        [
            'url' => '/help/accessibility',
            'label' => 'Accessibility',
        ],
        [
            'url' => '/privacy/cookies',
            'label' => 'Cookies',
            'active' => true, // This link is active
        ],
        [
            'url' => 'https://www.gov.uk/government/organisations/ministry-of-justice',
            'label' => 'Contacts',
        ],
        [
            'url' => '/copyright',
            'label' => 'Copyright',
        ],
        [
            'url' => '/help',
            'label' => 'Help',
        ],
        [
            'url' => '/privacy',
            'label' => 'Privacy',
        ],
        [
            'url' => '/ministry-of-justice-webchats',
            'label' => 'Webchats',
        ],
        [
            'url' => '/website-queries',
            'label' => 'Website queries',
        ],
    ]
];

return $component;
