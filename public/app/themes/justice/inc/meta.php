<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class Meta
{

    public function registerHooks()
    {
        add_action('init', [$this, 'registerMeta']);
        add_action('init', [$this, 'registerPanelMeta']);
    }

    /**
     * Register fields
     */
    
    public function registerMeta()
    {
        register_post_meta('page', '_short_title', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'description' => 'Short title for use in navigation, previously called Short Name. If not set, the page title will be used.',
            // This is needed because the meta is protected. i.e. prefixed with _
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            }
        ]);
    }

    public function registerPanelMeta()
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

        register_post_meta('page', '_panel_archived', $default_meta_settings);
        
        register_post_meta('page', '_panel_brand', array_merge($default_meta_settings, ['default' => true]));

        register_post_meta('page', '_panel_direct_gov', $default_meta_settings);

        register_post_meta('page', '_panel_email_alerts', $default_meta_settings);

        register_post_meta('page', '_panel_search', $default_meta_settings);
    }

    public function hasPanel(string $panel, int $post_id): bool
    {
        return get_post_meta($post_id, "_panel_$panel", true);
    }

    /**
     * Get short title
     */

    public function getShortTitle(string | int $post_id): string
    {
        $short_title = get_post_meta($post_id, 'short_title', true);

        return $short_title ? $short_title : get_the_title($post_id);
    }
}
