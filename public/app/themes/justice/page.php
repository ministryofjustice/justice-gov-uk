<?php
/**
 * The template for displaying basic page
 *
 */

use MOJ\Justice\PostMeta;

get_header();
get_footer();

$post_meta = new PostMeta(get_the_ID());

$sidePanelsRight = [];
$sidePanelsLeft = [];

$allowedRightPanels = [
    'brand' => '',
    'search' => 'search',
    'email_alerts' => 'email_alerts',
    'related' => 'list',
    'archived' => 'archive'
];

if ($post_meta->sideHasPanels('right')) {
    if ($post_meta->hasPanel('menu')) {
        foreach ($allowedRightPanels as $panel) {

        }
    }
}
if ($post_meta->sideHasPanels('left')) {

}

$templates = array( 'templates/basic.html.twig');
$context = Timber::context([
    'content' => get_the_content(),
    'side_panels_right' => $sidePanelsRight,
    'side_panels_left' => $sidePanelsLeft,
]);
Timber::render($templates, $context);
