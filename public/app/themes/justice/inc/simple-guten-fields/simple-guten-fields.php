<?php
/**
 * Name:            Simple Guten Fields
 * Description:     Simple Guten Fields is a concept of adding custom fields to Gutenberg editor.
 * @see https://bebroide.medium.com/how-to-easily-develop-with-react-your-own-custom-fields-within-gutenberg-wordpress-editor-b868c1e193a9
 */

namespace MOJ\Justice;

include('register-fields.php');

class SimpleGutenFields
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'loadScripts']);
        add_filter('rest_api_init', [$this, 'metaFields'], 0);
    }

    /**
     * Load scripts.
     */
    public function loadScripts()
    {
        $dir = __DIR__;

        $script_asset_path = "$dir/../../dist/block-editor.asset.php";
        if (! file_exists($script_asset_path)) {
            throw new \Error(
                'You need to run `npm start` or `npm run build` for the "create-block/simple-guten-fields" block first.'
            );
        }

        $script_asset = require($script_asset_path);
        wp_register_script(
            'sgf-script',
            get_template_directory_uri() . '/dist/block-editor.js',
            $script_asset['dependencies'],
            $script_asset['version']
        );

        $fields = apply_filters('sgf_register_fields', []);
        $data   = [
            'fields' => $fields,
        ];
        wp_localize_script('sgf-script', 'sgf_data', $data);

        wp_enqueue_script('sgf-script');
    }

    public function metaFields()
    {
        $fields_array = apply_filters('sgf_register_fields', []);
        foreach ($fields_array as $field) {
            // Ensure post type exists and field name is valid
            if (! $field['post_type'] || ! post_type_exists($field['post_type']) || ! $field['meta_key'] || ! is_string($field['meta_key'])) {
                return;
            }
    
    
            // Using Null Coalesce Operator to set defaults
            register_post_meta(
                $field['post_type'],
                $field['meta_key'],
                [
                    'type'              => $field['type'] ?? 'string',
                    'single'            => $field['single'] ?? true,
                    'default'           => $field['default'] ?? '',
                    'show_in_rest'      => $field['show_in_rest'] ?? true,
                    'control'           => $field['control'] ?? 'text',
                    // This is needed because the meta is protected. i.e. prefixed with _
                    'auth_callback'     => $field['auth_callback'] ?? function () {
                        return current_user_can('edit_posts');
                    },
                    'sanitize_callback' => $field['sanitize_callback'] ?? 'rest_sanitize_text',
                ]
            );
        }
    }
}
