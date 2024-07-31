<?php
/**
 * The template for displaying 401 pages
 *
 */

get_header();
get_footer();

$pageTitle = 'Test';

$templates = array( 'templates/home.html.twig');
$context = Timber::context([
    'pageTitle' => [
        'text' => get_the_title(),
        'tag' => 'h1',
        'url' => '',
    ],
    'mainImage' => [
        'url' => get_template_directory_uri() . '/dist/img/scales-of-justice.jpg',
        'alt' => 'Scales of justice',
    ],
    'content' => get_the_content(),
]);
Timber::render($templates, $context);
