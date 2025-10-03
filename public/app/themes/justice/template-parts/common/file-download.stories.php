<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/FileDownload',
    'stories' => []
];

$component['stories']['Default'] = [
    'url' => '#',
    'format' => 'PDF',
    'label' => 'Example Document',
    'filesize' => '1.2 MB',
];

$component['stories']['WithLanguage'] = [
    ...$component['stories']['Default'],
    'language' => 'Welsh',
];

$component['stories']['WordDocument'] = [
    ...$component['stories']['Default'],
    'format' => 'DOC',
];

$component['stories']['Basic'] = [
    ...$component['stories']['Default'],
    'format' => null,
    'filesize' => null,
];

$component['stories']['InParagraph'] = [
    ...$component['stories']['Default'],
    'decorator' => "<p>
        The __Story__ follows the __Story__ and __Story__ PD Updates, 
        which made provision to require represented claimants to use the DCP.
    </p>"
];

return $component;
