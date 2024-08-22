<?php
/**
 * The template for displaying basic page
 *
 */

use MOJ\Justice\PostMeta;
use MOJ\Justice\DynamicMenu;
use MOJ\Justice\Content;

get_header();
get_footer();

$postMeta = new PostMeta(get_the_ID());

$contentHelper = new Content();

$breadcrumbs = array_map(function ($link) {
    return [
        'label' => $link['title'],
        'url' => $link['url'],
    ];
}, (new MOJ\Justice\Breadcrumbs)->getTheBreadcrumbs());

$relatedPages = $postMeta->getMeta('_panel_related_entries');
$otherWebsites = array_map(function ($link) use ($contentHelper) {
    return [
        'label' => $link['label'],
        'url' => $link['url'],
        'newTab' => $contentHelper->isExternal($link['url'])
    ];
}, $postMeta->getMeta('_panel_other_websites_entries'));

$sidePanelsRight = [];
$sidePanelsLeft = [];

$allowedRightPanels = [
    'brand' => [
        'variant' => 'brand',
    ],
    'search' => [
        'variant' => 'form',
        'title' => 'Search this collection',
        'description' => 'Search standard directions',
        'form' => [
            'id' => 'search-bar-sidebar',
            'action' => '/search',
            'input' => [
                'labelHidden' => true,
                'label' => 'Search the standard directions content',
                'id' => 'search-bar-sidebar-input',
                'name' => 'query',
            ],
            'button' => [
                'text' => 'Search',
            ],
            'hiddenInputs' => [
                [
                    'name' => 'parent',
                    'value' => '153',
                ],
            ],
        ],
    ],
    'email_alerts' => [
        'variant' => 'form',
        'title' => 'Get email alerts',
        'description' => 'Enter your email address below to subscribe to email alerts',
        'form' => [
            'id' => 'email-alerts-sidebar',
            'action' => 'https://public.govdelivery.com/accounts/UKMOJ/subscribers/qualify',
            'input' => [
                'name' => 'email',
                'labelHidden' => true,
                'label' => 'Enter email address:',
                'id' => 'email-alerts-input',
            ],
            'button' => [
                'text' => 'Subscribe',
            ],
        ],
    ],
    ... $relatedPages ? ['related' => [
        'variant' => 'list',
        'title' => 'Related pages',
        'links' => $relatedPages,
    ]] : [],
    ... $otherWebsites ? ['other_websites' => [
        'variant' => 'list',
        'title' => 'Other websites',
        'links' => $otherWebsites,
    ]] : [],
    'archived' => [
        'variant' => 'archive',
        'title' => 'Archived pages',
        'links' => [
            [
                'url' => 'https://webarchive.nationalarchives.gov.uk/*/http://www.justice.gov.uk/index.htm',
                'label' => 'Ministry of Justice archived websites (2007 to 2012)',
                'newTab' => true,
            ],
            [
                'url' => 'href="https://webarchive.nationalarchives.gov.uk/ukgwa/timeline/https:/www.justice.gov.uk/courts/procedure-rules',
                'label' => 'Ministry of Justice archived websites (2012 to present day)',
                'newTab' => true,
            ],
        ],
    ],
];



if ($postMeta->sideHasPanels('right')) {
    foreach ($allowedRightPanels as $panel => $variant) {
        if ($postMeta->hasPanel($panel)) {
            $sidePanelsRight[$panel] = $variant;
        }
    }
}
if ($postMeta->sideHasPanels('left')) {
    if ($postMeta->hasPanel('menu')) {
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

// If there are no side panels on the left use the single sidebar template
$templates = !$sidePanelsLeft ? ['templates/basic--one-sidebar.html.twig'] : ['templates/basic--two-sidebars.html.twig'];
$context = Timber::context([
    'title' => get_the_title(),
    'breadcrumbs' => $breadcrumbs,
    'updatedDate' => $postMeta->getMeta('_show_updated_at') ? $postMeta->getModifiedAt() : null,
    'sidePanelsRight' => $sidePanelsRight,
    'sidePanelsLeft' => $sidePanelsLeft,
]);
Timber::render($templates, $context);
