<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 */

//body_class();
//wp_body_open();

get_header();

$errorTitle = 'Website temporarily unavailable';
$errorCode = '503';
$errorMessage = 'Service unavailable';
$errorContent = '
        <p>Sorry – this website is temporarily unavailable while we perform essential maintenance.</p>
        <p>We’ll be back soon.</p>
        <p>For urgent inquiries contact,</p>
        <a href="mailto:web.comments@justice.gov.uk">web.comments@justice.gov.uk</a>
';

$homeUrl = home_url();
$permalink = get_permalink();

$templates = array( 'templates/error.html.twig');
$context = Timber::context([
    'title' => $errorTitle,
    'errorCode' => $errorCode,
    'errorMessage' => $errorMessage,
    'content' => $errorContent,
    'homeUrl' => $homeUrl,
    'permalink' => $permalink,
]);
Timber::render($templates, $context);
