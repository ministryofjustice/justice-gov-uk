<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}



class PostMeta
{

    protected int | null | false $post_id = null;

    /**
     * Meta groups and fields
     * These are the meta groups and fields that will be registered.
     * They will also be used to generate the meta boxes (via JS) in the admin,
     * it's important to make sure the type matches MetaGroup type in block-editor.d.ts
     */

    public array $meta_groups = [
         [
            'name'  => 'panel',
            'title' => 'Panels',
            'fields' => [
                [
                    'name'  => '_panel_archived',
                    'label' =>'Show archived panel',
                    'settings' => [
                        'type'  => 'boolean',
                    ],
                ],
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
                    'name'  => '_panel_direct_gov',
                    'label' => 'Show direct gov panel',
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
                    'name'  => '_panel_search',
                    'label' => 'Show search panel',
                    'settings' => [
                        'type'  => 'boolean',
                    ],
                ]
            ]
         ],
         [
            'name' => 'page-meta',
            'title' => 'Page meta',
            'fields' => [
                [
                    'name'  => '_short_title',
                    'label' => 'Short title',
                    'settings' => [
                        'type'  => 'string',
                        'default' => '',
                    ]
                ]
            ]
         ]
    ];

    public function __construct(int | string $post_id = 0)
    {
        $this->post_id = $post_id ? (int) $post_id : \get_the_ID();
    }

    public function registerHooks()
    {
        add_action('init', [$this, 'registerMetaGroups']);
    }

    /**
     * Register fields
     */
    

    public function registerMetaGroups()
    {

        $default_meta_settings = [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => false,
            // This is needed because the meta is protected. i.e. prefixed with _
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            }
        ];

        // Loop over $meta_groups
        foreach ($this->meta_groups as $meta_group) {
            foreach ($meta_group['fields'] as $field) {
                register_post_meta(
                    'page',
                    $field['name'],
                    array_merge($default_meta_settings, $field['settings'])
                );
            }
        }
    }

    /**
     * Check if a panel is enabled
     */


    public function hasPanel(string $panel, string | int $post_id = 0): bool
    {
        return get_post_meta($post_id ?: $this->post_id, "_panel_$panel", true);
    }

    /**
     * Get short title
     */

    public function getShortTitle(string | int $post_id = 0): string
    {
        $short_title = get_post_meta($post_id ?: $this->post_id, 'short_title', true);

        return $short_title ? $short_title : get_the_title($post_id);
    }
}
