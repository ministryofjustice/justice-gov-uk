<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/SelectionInput',
    'layout' => 'sidebar',
    'stories' => []
];

$component['stories']['Default'] = [
    'title' => 'Selection Input',
    'group' => 'example-group',
    'options' => [
        [
            'value' => 'option1',
            'label' => 'Option 1',
            'checked' => true,
        ],
        [
            'value' => 'option2',
            'label' => 'Option 2',
        ],
        [            'value' => 'option3',
            'label' => 'Option 3',
        ],
    ],
    'hint' => 'Select one option',
];

$component['stories']['Vertical'] = [
    ...$component['stories']['Default'],
    'direction' => 'vertical',
];

$component['stories']['Checkboxes'] = [
    ...$component['stories']['Default'],
    'type' => 'checkbox',
];

$component['stories']['WithError'] = [
    ...$component['stories']['Default'],
    'error' => true,
    'error_text' => 'This is an error message',
];

return $component;
