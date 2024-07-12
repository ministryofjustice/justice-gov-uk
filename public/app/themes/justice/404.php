<?php
/**
 * The template for displaying 404 pages
 *
 */

get_header();

$errorTitle = 'Page not found';
$errorCode = '404';
$errorMessage = 'Page not found';
$errorContent = '
        <h2>Try:</h2>
        <ul>
            <li>Checking that there are no typos in the page address.</li>
            <li>You can also use the <a href="#">search</a> or <a href="#">browse from the homepage</a> to find the information you need.</li>
            <li>If you\'ve reached this page by clicking on a link or file, it is likely that the item has been moved or deleted. Contact the editor team to let them know they\'ve got a broken link, <a href="#">web.comments@justice.gov.uk</a> and see if they can help you find what you were looking for.</li>
            <li>Retry your search using alternative words in case the document or page has been moved or renamed.</li>
        </ul>
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
