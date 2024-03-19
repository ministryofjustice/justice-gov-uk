<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A php class related to theme assets.
 */

class ThemeAssets
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks() : void
    {
        add_action('wp_enqueue_scripts', array($this, 'loadScripts'));
        add_action('wp_enqueue_scripts', array($this, 'loadStyles'));
    }

    /**
     * Load the main app script.
     * 
     * This is also the best place to load any other scripts that are needed on every page.
     * After wp_register_script, localise_script can be used to pass data to the script if necessary.
     * 
     * @return void
     */

    public function loadScripts(): void
    {

        $script_asset_path = get_template_directory() . "/dist/php/app.min.asset.php";

        if (!file_exists($script_asset_path)) {
            throw new \Error(
                'You need to run `npm start` or `npm run build` for "app" first.'
            );
        }

        $script_asset = require($script_asset_path);
        wp_register_script(
            'moj-justice-app',
            get_template_directory_uri() . '/dist/app.min.js',
            $script_asset['dependencies'],
            $script_asset['version']
        );

        wp_enqueue_script('moj-justice-app');
    }

    /**
     * Load the main app styles.
     * 
     * @return void
     */

    public function loadStyles(): void
    {
        wp_enqueue_style('justice-styles', get_template_directory_uri() . '/dist/css/app.min.css');
    }
}
