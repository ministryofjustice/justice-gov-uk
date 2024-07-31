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
        add_action('wp_enqueue_scripts', [$this, 'loadStyles']);
        add_action('wp_enqueue_scripts', [$this, 'loadScripts']);
        add_action('wp_default_scripts', [$this, 'removeJqueryMigrate']);
    }

    /**
     * Load the main app styles.
     *
     * @return void
     */

    public function loadStyles(): void
    {
        // Legacy
        // wp_enqueue_style('justice-styles', get_template_directory_uri() . '/dist/css/app.min.css');
        // Frontend
        wp_enqueue_style('justice-frontend-styles', home_url('/app/frontend/dist/main.css'));
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
        // Legacy
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
        
        // Frontend
        wp_enqueue_script('justice-frontend-script', home_url('/app/frontend/dist/main.js'), [], null, true);
    }

    /**
     * Remove the unnecessary jquery migrate script.
     *
     * @param object $scripts
     * @return void
     */

    public function removeJqueryMigrate($scripts): void
    {
        if (! is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];

            if ($script->deps) {
                // Check whether the script has any dependencies
                $script->deps = array_diff($script->deps, array( 'jquery-migrate' ));
            }
        }
    }
}
