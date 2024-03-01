<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

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
     * Meta fields.
     *
     * Examples of fields are:
     * - `_short_title` for showing in menus
     * - `_modified_at_override` for manual entry of the modified at date.
     * - text fields for managing meta data in the head of the html.
     * - text fields to allow filtering of search results.
     */

    public function metaFields($fields_array)
    {

        $fields_array[] = [
            'meta_key' => '_short_title',
            'panel'    => 'meta_data',
        ];

        $fields_array[] = [
            'meta_key' => '_modified_at_override',
            'label'    => 'Modified at (override)',
            'type'     => 'string',
            'control'  => 'datepicker',
            'panel'    => 'meta_data',
        ];

        $fields_array[] = [
            'single'       => true,
            'meta_key'     => '_regions',
            'label'         => 'Regions',
            'control'      => 'multiselect',
            'type'         => 'array',
            'options'      => $this->values_regions,
            'panel'    => 'meta_data',
            'show_in_rest' => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'number'
                    ],
                ],
            ]
        ];

        $fields_array   = array_map(function ($field) {
            $field['post_type'] = $field['post_type'] ?? 'page';
            $field['control']   = $field['control'] ?? 'text';
            $field['panel']     = $field['panel'] ?? 'custom-fields';
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

        $link_schema = [
            'type'       => 'object',
            'properties' => [
                'label' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'url'       => [
                    'type' => 'string',
                    'default' => '',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key' => '_panel_brand',
            'type'     => 'boolean',
            'default'  => true,
            'control'  => 'toggle',
            'label'    => 'Show brand panel',
            'panel'    => 'panels',
            'conditions'   => [
                [
                    'target' => 'attribute.template',
                    'operator' => '!==',
                    'value'    => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key' => '_panel_search',
            'type'     => 'boolean',
            'default'  => false,
            'control'  => 'toggle',
            'label'    => 'Show search panel',
            'panel'    => 'panels',
            'conditions'   => [
                [
                    'target' => 'attribute.template',
                    'operator' => '!==',
                    'value'    => 'page_home.php',
                ],
            ],
        ];

        // $fields_array[] = [
        //     'meta_key' => '_panel_email_alerts',
        //     'type'     => 'boolean',
        //     'default'  => false,
        //     'control'  => 'toggle',
        //     'label'    => 'Show email alerts panel',
        //     'panel'    => 'panels',
        // ];

        $fields_array[] = [
            'meta_key' => '_panel_archived',
            'type'     => 'boolean',
            'default'  => false,
            'control'  => 'toggle',
            'label'    => 'Show archived panel',
            'panel'    => 'panels',
            'conditions'   => [
                [
                    'target' => 'attribute.template',
                    'operator' => '!==',
                    'value'    => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key' => '_panel_related',
            'type'     => 'boolean',
            'default'  => false,
            'control'  => 'toggle',
            'label'    => 'Show related pages panel',
            'panel'    => 'panels',
            'conditions'   => [
                [
                    'target' => 'attribute.template',
                    'operator' => '!==',
                    'value'    => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key' => '_panel_popular',
            'type'     => 'boolean',
            'default'  => true,
            'control'  => 'toggle',
            'label'    => 'Show most popular panel',
            'panel'    => 'panels',
            'conditions'   => [
                [
                    'target' => 'attribute.template',
                    'operator' => '===',
                    'value'    => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'     => '_panel_related_entries',
            'label'        => 'Related pages',
            'control'      => 'repeater',
            'type'         => 'array',
            'default'      => [],
            'panel'        => 'panels',
            'conditions'   => [
                [
                    'target' => 'attribute.template',
                    'operator' => '!==',
                    'value'    => 'page_home.php',
                ],
                [
                    'target' => 'attribute.meta._panel_related',
                    'operator' => '===',
                    'value'    => true,
                ],
            ],
            'show_in_rest' => [
                'schema' => [
                    'items' => $link_schema
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key' => '_panel_other_websites',
            'type'     => 'boolean',
            'default'  => false,
            'control'  => 'toggle',
            'label'    => 'Show other websites panel',
            'panel'    => 'panels',
            'conditions'   => [
                [
                    'target' => 'attribute.template',
                    'operator' => '!==',
                    'value'    => 'page_home.php',
                ],
            ],
        ];

        $fields_array[] = [
            'meta_key'     => '_panel_other_websites_entries',
            'label'        => 'Other websites',
            'control'      => 'repeater',
            'type'         => 'array',
            'default'      => [],
            'panel'        => 'panels',
            'conditions'   => [
                [
                    'target' => 'attribute.template',
                    'operator' => '!==',
                    'value'    => 'page_home.php',
                ],
                [
                    'target' => 'attribute.meta._panel_other_websites',
                    'operator' => '===',
                    'value'    => true,
                ],
            ],
            'show_in_rest' => [
                'schema' => [
                    'items' => $link_schema
                ],
            ],
        ];

        $fields_array   = array_map(function ($field) {
            $field['post_type'] = $field['post_type'] ?? 'page';
            $field['control']   = $field['control'] ?? 'text';
            $field['panel']     = $field['panel'] ?? 'custom-fields';
            $field['label']     = $field['label'] ?? ucfirst(str_replace('_', ' ', $field['meta_key']));

            return $field;
        }, $fields_array);

        return $fields_array;
    }
}
