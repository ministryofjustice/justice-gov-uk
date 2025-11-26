<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/Image',
    'layout' => 'medium',
    'stories' => []
];

$component['stories']['Default'] = [
    'url' => '/app/themes/justice/dist/img/scales-of-justice.jpg',
    'alt' => 'A statue of the scales of justice',
];

return $component;
