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
        add_action('init', [$this, 'registerMetaGroups']);
    }


    /**
     * Localize the block editor.
     * This will make the meta groups available as a global variable called justiceBlockEditorLocalized,
     * it's needed to make the fields available to the block editor. 
     * The alternative is for the the declaration to be also made in JS.
     */

    public function localize() {
        wp_localize_script('justice-block-editor', 'justiceBlockEditorLocalized', $this->meta_groups);
    }

    /**
     * Register fields.
     */

    public function registerMetaGroups()
    {

        // Our default settings for register_post_meta
        $default_meta_settings = [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => false,
            // This is needed because the meta is protected. i.e. prefixed with _
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
            'sanitize_callback' => 'rest_sanitize_boolean',
        ];

        // Loop over $meta_groups
        foreach ($this->meta_groups as $meta_group) {
            // Loop over the fields in each group
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
     * Check if a panel is enabled.
     */

    public function hasPanel(string $panel): bool
    {
        return get_post_meta( $this->post_id, "_panel_$panel", true);
    }

    /**
     * Get short title.
     */

    public function getShortTitle(): string
    {
        $short_title = get_post_meta( $this->post_id, '_short_title', true);

        return $short_title && strlen($short_title) ? $short_title : get_the_title($this->post_id);
    }
}
