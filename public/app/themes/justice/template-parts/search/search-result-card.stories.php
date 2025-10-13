<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/SearchResultCard',
    'stories' => []
];

$component['stories']['Default'] = [
    'title' => 'Practice Direction 52D – statutory appeals and appeals subject to special provision',
    'date' => '19 September 2012',
    'url' => 'https://www.justice.gov.uk/courts/procedure-rules/civil/rules/part19',
    'description' => "<span>…the <strong>test</strong> claim. (2) Where an order is made under paragraph (1), any order made in the <strong>test</strong> claim before the date of substitution is binding in the substituted claim…</span>"
];

$component['stories']['Document'] = [
    ...$component['stories']['Default'],
    'is_document' => true,
    'format' => 'PDF',
    'filesize' => '1.2 MB',
];

return $component;
