<?php
/**
 * The template for displaying 401 pages
 *
 */

get_header();

$errorTitle = 'Access to the requested resource has been denied';
$errorCode = '401';
$errorMessage = 'Authorisation Required';
$errorContent = '
      <h2>Troubleshooting</h2>
      <p>If you feel that you have reached this page in error or believe that you should have access to this resource
        please contact the editor team <a href="mailto:web.comments@justice.gov.uk">web.comments@justice.gov.uk</a>
      </p>
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
