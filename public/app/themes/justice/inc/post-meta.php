<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class PostMeta
{

    protected int | false $post_id = 0;

    /**
     * Meta groups and fields.
     * These are the meta groups and fields that will be registered.
     * They will also be used to generate the meta boxes (via JS) in the pose edit screen,
     * it's important to make sure the type matches MetaGroup type in block-editor.d.ts
     */
    
    public array $meta_groups = [
        [
            'name' => 'page-meta',
            'title' => 'Page meta',
            'fields' => [
                [
                    'name'  => '_short_title',
                    'label' => 'Short title',
                    'settings' => [
                        'type'              => 'string',
                        'default'           => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ]
                ]
            ]
        ],
        [
            'name'  => 'panel',
            'title' => 'Panels',
            'fields' => [
                [
                    'name'  => '_panel_brand',
                    'label' => 'Show brand panel',
                    'type'  => 'boolean',
                    'settings' => [
                        'type'  => 'boolean',
                        'default'  => true,
                    ]
                ],
                [
                    'name'  => '_panel_search',
                    'label' => 'Show search panel',
                    'settings' => [
                        'type'  => 'boolean',
                    ],
                ],
                [
                    'name'  => '_panel_email_alerts',
                    'label' => 'Show email alerts panel',
                    'settings' => [
                        'type'  => 'boolean',
                    ],
                ],
                [
                    'name'  => '_panel_archived',
                    'label' =>'Show archived panel',
                    'settings' => [
                        'type'  => 'boolean',
                    ],
                ],
            ]
         ]
    ];

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
            'type'     => 'string',
            'control'  => 'datepicker',
        ];

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
            'default'  => true,
            'control'  => 'toggle',
            'label'    => 'Show search panel',
            'panel'    => 'panels',
        ];
        
        $fields_array[] = [
            'meta_key' => '_panel_email_alerts',
            'type'     => 'boolean',
            'default'  => true,
            'control'  => 'toggle',
            'label'    => 'Show email alerts panel',
            'panel'    => 'panels',
        ];
        
        $fields_array[] = [
            'meta_key' => '_panel_archived',
            'type'     => 'boolean',
            'default'  => true,
            'control'  => 'toggle',
            'label'    => 'Show archived panel',
            'panel'    => 'panels',
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
}
