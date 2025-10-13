<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/SearchResultList',
    'stories' => []
];

$component['stories']['Default'] = [
    'cards' => [
        [

            'title' => 'Practice Direction 52D – statutory appeals and appeals subject to special provision',
            'date' => '19 September 2012',
            'url' => 'https://www.justice.gov.uk/courts/procedure-rules/civil/rules/part19',
            'description' => "<span>…the <strong>test</strong> claim. (2) Where an order is made under paragraph (1), any order made in the <strong>test</strong> claim before the date of substitution is binding in the substituted claim…</span>"
        ],
        [
            'title' => 'Civil - Civil Procedure Rules',
            'url' => 'https://www.justice.gov.uk/courts/procedure-rules/civil',
            'date' => '26 February 2019',
            'description' => '…testing a new bill of costs, Precedent AA, to reflect the costs',
            'is_document' => true,
            'format' => 'PDF',
            'filesize' => '1.2 MB',
        ]
    ]
];

$component['stories']['NoQuery'] = [
    ...$component['stories']['Default'],
    'cards' => [],
];

$component['stories']['NoResults'] = [
    ...$component['stories']['Default'],
    'cards' => [],
    'query' => 'non-existent search term'
];

return $component;
