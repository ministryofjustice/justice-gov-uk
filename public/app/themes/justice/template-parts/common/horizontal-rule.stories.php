<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/HorizontalRule',
    'stories' => []
];

$component['stories']['Default'] = [
    ];

$component['stories']['Decorative'] = [
    ...$component['stories']['Default'],
    'decorative' => true,
];

$component['stories']['FullWidth'] = [
    ...$component['stories']['Default'],
    'full_width' => true,
];

return $component;
