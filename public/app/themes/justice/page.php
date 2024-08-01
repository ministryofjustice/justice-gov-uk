<?php
/**
 * The template for displaying basic page
 *
 */

use MOJ\Justice\PostMeta;
use MOJ\Justice\DynamicMenu;

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
    if ($post_meta->hasPanel('menu')) {
        $links = (new DynamicMenu)->getTheNavigation();
        $sidePanelsLeft['menu'] = [
            'title' => 'Justice UK',
            'id' => '#main-page-content',
            'links' => array_map(function ($link) {
                return [
                    'label' => $link['title'],
                    'link' => $link['url'],
                    'active' => isset($link['selected']),
                ];
             }, $links)
        ];
    }
}

$templates = array( 'templates/basic.html.twig');
$context = Timber::context([
    'title' => get_the_title(),
    'content' => get_the_content(),
    'sidePanelsRight' => $sidePanelsRight,
    'sidePanelsLeft' => $sidePanelsLeft,
]);
Timber::render($templates, $context);
