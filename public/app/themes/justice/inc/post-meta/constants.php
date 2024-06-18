<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class PostMetaConstants
{


    /**
     * Values for dropdown(s).
     */

    public $values_regions =  [
        ['label' => 'England', 'value' => '0'],
        ['label' => 'Wales', 'value' => '1'],
    ];

    /**
     * Schemas.
     */

    private $link_schema = [
        'type'       => 'object',
        'properties' => [
            'label' => [
                'type'      => 'string',
                'default'   => '',
            ],
            'url' => [
                'type'      => 'string',
                'default'   => '',
            ],
        ],
    ];

    /**
     * Navigation fields.
     *
     * Examples of fields are:
     * - `_short_title` for showing in menus
     * - repeater for appending entries to the dynamic menu
     */

    public function navigationFields($fields_array)
    {

        $fields_array[] = [
            'meta_key'  => '_short_title',
            'help'      =>
            'Optional. This is used in breadcrumb & menu navigation. ' .
                'Default: Page title.'
        ];

        $fields_array[] = [
            'meta_key'  => '_title_tag',
            'help'      =>
            'Optional. Used for search result titles (internal & external) and browser tabs. ' .
                'Default: Page title.'
        ];

        $fields_array[] = [
            'meta_key'  => '_dynamic_menu_additional',
            'label'     => 'Append entries to menu.',
            'type'      => 'boolean',
            'default'   => false,
            'control'   => 'toggle',
            'help'      => 'Do you need custom entries on the left hand side menu?',
            'conditions'   => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ]
            ],
        ];

        $fields_array[] = [
            'meta_key'      => '_dynamic_menu_additional_entries',
            'control'      => 'repeater',
            'type'         => 'array',
            'default'      => [],
            'show_in_rest' => [
                'schema' => [
                    'items' => $this->link_schema
                ],
            ],
            'conditions'   => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
                [
                    'target'    => 'attribute.meta._dynamic_menu_additional',
                    'operator'  => '===',
                    'value'     => true,
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'  => '_dynamic_menu_exclude_this',
            'label'     => "Exclude this page from it's parent's menu.",
            'type'      => 'boolean',
            'default'   => false,
            'control'   => 'toggle',
            'help'      => "Do you want this page to be hidden from it's parent's left hand side menu?",
            'conditions'   => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
                [
                    'target'    => 'attribute.tags',
                    'operator'  => 'NOT INTERSECTS',
                    'value'     => (new DynamicMenu())->getExcludedChildPagesTags(),
                ],
            ],
        ];

        $fields_array   = array_map(function ($field) {
            $field['post_type'] = $field['post_type'] ?? 'page';
            $field['control']   = $field['control'] ?? 'text';
            $field['panel']     = $field['panel'] ?? 'navigation';
            $field['label']     = $field['label'] ?? ucfirst(str_replace('_', ' ', $field['meta_key']));

            return $field;
        }, $fields_array);

        return $fields_array;
    }

    /**
     * Meta fields.
     *
     * - `_modified_at_override` for manual entry of the modified at date.
     * - text fields for managing meta data in the head of the html.
     * - text fields to allow filtering of search results.
     */

    public function metaFields($fields_array)
    {

        $fields_array[] = [
            'meta_key'  => '_show_updated_at',
            'label'     => 'Show updated at',
            'type'      => 'boolean',
            'default'   => true,
            'control'   => 'toggle',
            'help'      => 'Show updated at date following the content.',
            'conditions'   => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ]
            ],
        ];

        $fields_array[] = [
            'meta_key'  => '_modified_at_override',
            'label'     => 'Modified at (override)',
            'type'      => 'string',
            'control'   => 'datepicker',
            'help'      =>
            'Optional. Updated at date can be set here. ' .
                'Default: Most recent update.',
            'conditions'   => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ]
            ],
        ];

        $fields_array[] = [
            'single'        => true,
            'meta_key'      => '_regions',
            'label'         => 'Regions',
            'control'       => 'multiselect',
            'type'          => 'array',
            'options'       => $this->values_regions,
            'help'          => 'Optional. Regions for this content.',
            'show_in_rest'  => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'number'
                    ],
                ],
            ],
            'conditions'   => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ]
            ],
        ];

        $fields_array   = array_map(function ($field) {
            $field['post_type'] = $field['post_type'] ?? 'page';
            $field['control']   = $field['control'] ?? 'text';
            $field['panel']     = $field['panel'] ?? 'meta_data';
            $field['label']     = $field['label'] ?? ucfirst(str_replace('_', ' ', $field['meta_key']));

            return $field;
        }, $fields_array);

        return $fields_array;
    }

    /**
     * Panel fields.
     *
     * Examples of fields are:
     * - toggle fields for displaying panels on the right hand side.
     * - entry fields for managing the content of the panels.
     */

    public function panelFields($fields_array)
    {

        $fields_array[] = [
            'meta_key'      => '_panel_menu',
            'type'          => 'boolean',
            'default'       => true,
            'control'       => 'toggle',
            'label'         => 'Show menu',
            'help'          => 'Show the navigation menu on the left hand side.',
            'conditions'    => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'      => '_panel_brand',
            'type'          => 'boolean',
            'default'       => true,
            'control'       => 'toggle',
            'label'         => 'Show brand panel',
            'help'          => 'Show the logo on the right hand side.',
            'conditions'    => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'      => '_panel_search',
            'type'          => 'boolean',
            'default'       => false,
            'control'       => 'toggle',
            'label'         => 'Show search panel',
            'help'          => 'Show the Standard Direction search on the right hand side.',
            'conditions'    => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
            ],
        ];

        // $fields_array[] = [
        //     'meta_key' => '_panel_email_alerts',
        //     'type'     => 'boolean',
        //     'default'  => false,
        //     'control'  => 'toggle',
        //     'label'    => 'Show email alerts panel',
        // ];

        $fields_array[] = [
            'meta_key'      => '_panel_archived',
            'type'          => 'boolean',
            'default'       => false,
            'control'       => 'toggle',
            'label'         => 'Show archived panel',
            'help'          => 'Show a link to nationalarchives.gov.uk on the right hand side.',
            'conditions'    => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'      => '_panel_related',
            'type'          => 'boolean',
            'default'       => false,
            'control'       => 'toggle',
            'label'         => 'Show related pages panel',
            'help'          => 'Show a list of user defined links to related pages on the right hand side.',
            'conditions'    => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'      => '_panel_popular',
            'type'          => 'boolean',
            'default'       => false,
            'control'       => 'toggle',
            'label'         => 'Show most popular panel',
            'conditions'    => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '===',
                    'value'     => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'     => '_panel_related_entries',
            'label'        => 'Related pages',
            'control'      => 'repeater',
            'type'         => 'array',
            'default'      => [],
            'show_in_rest' => [
                'schema' => [
                    'items' => $this->link_schema
                ],
            ],
            'conditions'   => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
                [
                    'target'    => 'attribute.meta._panel_related',
                    'operator'  => '===',
                    'value'     => true,
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'      => '_panel_other_websites',
            'type'          => 'boolean',
            'default'       => false,
            'control'       => 'toggle',
            'label'         => 'Show other websites panel',
            'help'          => 'Show a list of user defined links to other pages on the right hand side.',
            'conditions'    => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'     => '_panel_other_websites_entries',
            'label'        => 'Other websites',
            'control'      => 'repeater',
            'type'         => 'array',
            'default'      => [],
            'show_in_rest' => [
                'schema' => [
                    'items' => $this->link_schema
                ],
            ],
            'conditions'   => [
                [
                    'target'    => 'attribute.template',
                    'operator'  => '!==',
                    'value'     => 'page_home.php',
                ],
                [
                    'target'    => 'attribute.meta._panel_other_websites',
                    'operator'  => '===',
                    'value'     => true,
                ],
            ],
        ];

        $fields_array   = array_map(function ($field) {
            $field['post_type'] = $field['post_type'] ?? 'page';
            $field['control']   = $field['control'] ?? 'text';
            $field['panel']     = $field['panel'] ?? 'panels';
            $field['label']     = $field['label'] ?? ucfirst(str_replace('_', ' ', $field['meta_key']));

            return $field;
        }, $fields_array);

        return $fields_array;
    }
}
