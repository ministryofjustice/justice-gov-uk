<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/Button',
    'stories' => []
];

// Convert the above to php
$component['stories']['Primary'] = [
    'button_text' => 'Search',
    'variant'=> 'primary',
];


$component['stories']['Dark'] = [
    ...$component['stories']['Primary'],
    'variant'=> 'dark',
];

$component['stories']['Light'] = [
    ...$component['stories']['Primary'],
    'variant'=> 'light',
];

$component['stories']['Light'] = [
    ...$component['stories']['Primary'],
    'type' => 'input',
    'input_type' => 'submit',
];

return $component;
