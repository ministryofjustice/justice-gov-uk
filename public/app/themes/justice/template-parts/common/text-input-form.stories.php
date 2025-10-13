<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/SelectionInput',
    'layout' => 'sidebar',
    'stories' => []
];

$component['stories']['Default'] = [
    'id' => 'selection-input-default',
    'action' => '#',
    'input' => [
        'id' => 'selection-input-default',
        'name' => 'selection-input-default',
        'label' => 'Select an option',
        'label_hidden' => true,
    ],
    'hidden_inputs' => [],
    'button' => [
        'text' => 'Submit'
    ],
];

return $component;
