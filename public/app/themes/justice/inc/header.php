<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class Header
{
    const FORM_ARGS = [
        'id' => 'search-bar-header',
        'action' => '/search',
        'input' => [
            'id' => 'search-bar-header-input',
            'label' => 'Search',
            'label_hidden' => true,
            'name' => 's',
        ],
        'button' => [
            'text' => 'Search',
        ],
    ];

    public static function showSearch(): bool
    {
        return !is_search();
    }

    public static function getLinks(): array
    {

        return [
            [
                'label' => 'Courts',
                'url' => 'https://www.gov.uk/government/organisations/hm-courts-and-tribunals-service'
            ],
            [
                'label' => 'Procedure rules',
                'url' => get_home_url(null, '/courts/procedure-rules'),
                // Does the current permalink path start with /courts/procedure-rules?
                'active' => str_starts_with(get_permalink(), get_home_url(null, '/courts/procedure-rules'))
            ],
            [
                'label' =>  'Offenders',
                'url' => 'https://www.gov.uk/government/organisations/hm-prison-and-probation-service'
            ]
        ];
    }
}
