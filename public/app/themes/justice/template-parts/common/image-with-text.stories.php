<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/Image',
    'stories' => []
];

$component['stories']['Left'] = [
    'direction' => 'left',
    'title' => [
        'text' => 'Justice on GOV.UK',
        'tag' => 'h2',
        'url' => '#',
    ],
    'image' => [
        'url' => '/app/themes/justice/dist/img/scales-of-justice.jpg',
        'alt' => 'A statue of the scales of justice',
    ],
    'content' => "
        <p>This website now hosts Civil and Family Procedure Committee Rules content only. 
        You will be automatically redirected to equivalent content on 
        <a href=\"#\" target=\"_blank\" rel=\"noopener\">GOV.UK</a> or the National Archives.</p>
    ",
];

$component['stories']['Right'] = [
    ...$component['stories']['Left'],
    'direction' => 'right',
];

return $component;
