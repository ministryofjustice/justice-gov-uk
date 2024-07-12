<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 */

//body_class();
//wp_body_open();

get_header();

$errorTitle = 'Cannot connect to server';
$errorCode = '500';
$errorMessage = 'Server connection error';
$errorContent = '
         <p>There is a problem connecting to the server. You could try to refresh your page several times.</p>
         <p>If the problem persist please be patient, we are aware of the issue.</p>
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
