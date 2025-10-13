<?php

defined('IN_STORIES') || exit;

$component = [
    'title' => 'Components/RichText',
    'stories' => []
];

$component['stories']['Default'] = [
    'content' => '<p>This is a <strong>rich text</strong> example with <a href="https://example.com">links</a>, <em>emphasis</em>, and <code>code snippets</code>.</p>',
];

return $component;
