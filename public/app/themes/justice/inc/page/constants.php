<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class PageConstants
{
    const PANELS_LEFT =  [
        'menu' => [
            'title' => 'Justice UK',
            'id' => '#main-page-content',
            'links' => [],
        ],
    ];

    const PANELS_RIGHT =  [
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
                    'name' => 's',
                ],
                'button' => [
                    'text' => 'Search',
                ],
                'hiddenInputs' => [
                    [
                        'name' => 'parent',
                        'value' => '153', // TODO - what is this?
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
        'related' => [
            'variant' => 'list',
            'title' => 'Related pages',
            'links' => [],
        ],
        'other_websites' => [
            'variant' => 'list',
            'title' => 'Other websites',
            'links' => [],
        ],
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
                    'url' => 'https://webarchive.nationalarchives.gov.uk/ukgwa/timeline/https:/www.justice.gov.uk/courts/procedure-rules',
                    'label' => 'Ministry of Justice archived websites (2012 to present day)',
                    'newTab' => true,
                ],
            ],
        ],
    ];
}
