<?php
/**
 * The template for displaying 401 pages
 *
 */

get_header();

$templates = ['templates/home.html.twig'];
$blockContent = [
    'variant' => 'list',
    'title' => 'Most popular',
    'links' => [
        [
            'url' => home_url('/courts/procedure-rules'),
            'label' => 'Procedure rules',
        ],
        [
            'url' => 'https://www.gov.uk/government/collections/royal-courts-of-justice-and-rolls-building-daily-court-lists',
            'label' => 'Daily court lists',
        ],
        [
            'url' => 'https://www.gov.uk/government/collections/prisons-in-england-and-wales',
            'label' => 'Prison finder',
        ],
        [
            'url' => 'https://www.gov.uk/courts/crown-court',
            'label' => 'XHIBIT daily court status',
        ],
        [
            'url' => 'https://www.gov.uk/guidance/prison-service-instructions-psis',
            'label' => 'Prison service instructions (PSIs)',
        ],
        [
            'url' => 'https://www.gov.uk/guidance/probation-instructions-pis',
            'label' => 'Probation instructions',
        ],
    ],
];

$context = Timber::context([
    'pageTitle' => [
        'text' => get_the_title(),
        'tag' => 'h1',
    ],
    'mainImage' => [
        'url' => get_template_directory_uri() . '/dist/img/scales-of-justice.jpg',
        'alt' => 'Scales of justice',
    ],
    'blocks' => [$blockContent],
    'content' => get_the_content(),
]);
Timber::render($templates, $context);

get_footer();
