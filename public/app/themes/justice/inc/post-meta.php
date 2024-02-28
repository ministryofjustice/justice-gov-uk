<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class PostMeta
{

    protected int | false $post_id = 0;

    /**
     * Constructor.
     */

    public function __construct(int | string $post_id = 0)
    {
        $this->post_id = $post_id ? (int) $post_id : \get_the_ID();
    }

    /**
     * Register hooks.
     * This isn't called within the constructor because it's only needs to be called once.
     */

    public function registerHooks()
    {
        add_filter('sgf_register_fields', [$this, 'postFields'], 5);
    }

    public function postFields($fields_array)
    {

        $fields_array[] = [
            'meta_key' => '_short_title',
        ];

        $fields_array[] = [
            'meta_key' => '_modified_at_override',
            'label'    => 'Modified at (override)',
            'type'     => 'string',
            'control'  => 'datepicker',
        ];

        $fields_array[] = [
            'meta_key' => '_page_test',
            'label'    => 'Related page',
            'type'     => 'string',
            'control'  => 'page',
        ];

        /**
         * Panels - toggles
         */

        $fields_array[] = [
            'meta_key' => '_panel_brand',
            'type'     => 'boolean',
            'default'  => true,
            'control'  => 'toggle',
            'label'    => 'Show brand panel',
            'panel'    => 'panels',
        ];
        
        $fields_array[] = [
            'meta_key' => '_panel_search',
            'type'     => 'boolean',
            'default'  => false,
            'control'  => 'toggle',
            'label'    => 'Show search panel',
            'panel'    => 'panels',
        ];
        
        $fields_array[] = [
            'meta_key' => '_panel_email_alerts',
            'type'     => 'boolean',
            'default'  => false,
            'control'  => 'toggle',
            'label'    => 'Show email alerts panel',
            'panel'    => 'panels',
        ];
        
        $fields_array[] = [
            'meta_key' => '_panel_archived',
            'type'     => 'boolean',
            'default'  => false,
            'control'  => 'toggle',
            'label'    => 'Show archived panel',
            'panel'    => 'panels',
        ];

        $fields_array[] = [
            'meta_key' => '_panel_related',
            'type'     => 'boolean',
            'default'  => false,
            'control'  => 'toggle',
            'label'    => 'Show related pages panel',
            'panel'    => 'panels',
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
                    'meta_key' => '_panel_related',
                    'operator' => '===',
                    'value'    => true,
                ],
            ],
            'show_in_rest' => [
                'schema' => [
                    'items' => [
                        'type'       => 'object',
                        'properties' => [
                            'label' => [
                                'type' => 'string',
                                'control'  => 'page',
                                'default' => '',
                            ],
                        ],
                    ]
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
                    'meta_key' => '_panel_other_websites',
                    'operator' => '===',
                    'value'    => true,
                ],
            ],
            'show_in_rest' => [
                'schema' => [
                    'items' => [
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
                    ]
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

    /**
     * Check if a panel is enabled.
     */

    public function hasPanel(string $panel, string | int $post_id = 0): bool
    {
        return get_post_meta($post_id ?: $this->post_id, "_panel_$panel", true);
    }

    /**
     * Get short title.
     */

    public function getShortTitle(string | int $post_id = 0): string
    {
        $short_title = get_post_meta($post_id ?: $this->post_id, '_short_title', true);

        return $short_title && strlen($short_title) ? $short_title : get_the_title($post_id ?: $this->post_id);
    }

    /**
     * Get the modified at date.
     */

    public function getModifiedAt(string | int $post_id = 0): string
    {
        $date_format = 'l, j F Y';
        try {
            $modified_at_override = get_post_meta($post_id ?: $this->post_id, '_modified_at_override', true);
            return $modified_at_override ? date($date_format, strtotime($modified_at_override)) : get_the_modified_date($date_format);
        } catch (\Exception) {
            return get_the_modified_date($date_format);
        }
    }

    /**
     * Get the meta field.
     * A convenience wrapper around WordPress' get_post_meta.
     */

    public function getMeta(string $meta_key, string | int $post_id = 0, bool $single = true)
    {
        return get_post_meta($post_id ?: $this->post_id, $meta_key, $single);
    }
}
