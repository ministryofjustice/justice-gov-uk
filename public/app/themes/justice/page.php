<?php

defined('ABSPATH') || exit;

/**
 * The template for displaying a generic page
 */

use Timber\Timber;
use MOJ\Justice\PageController;

$page_controller = new PageController();

get_header();

$context = Timber::context([
    'title' => get_the_title(),
    'homeUrl' => home_url(),
    'permalink' => get_the_permalink(),
    'breadcrumbs' => (new MOJ\Justice\Breadcrumbs)->getTheBreadcrumbs(),
    'updatedDate' => $page_controller->getUpdatedAt(),
    'sidePanelsRight' => $page_controller->getRightSidePanels(),
    'sidePanelsLeft' => $page_controller->getLeftSidePanels(),
]);

$templates = [$page_controller->getTemplate()];

Timber::render($templates, $context);

get_footer();
