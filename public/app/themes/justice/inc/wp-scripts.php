<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

use WP_Scripts;

/**
 * Extends WP_Scripts class to filter inline script tags added via wp_localize_script().
 *
 * @link https://wordpress.stackexchange.com/a/378850
 *
 * @see justice/inc/wp-script-localization.php
 * @see justice/src/js/script-localization.js
 */
class WpFilterableScripts extends WP_Scripts
{

    public $l10n_store = [];

    /**
     * Executes the parent class constructor and initialization, then copies in the
     * pre-existing $wp_scripts contents
     */
    public function __construct()
    {
        parent::__construct();

        if (function_exists('is_admin') && ! is_admin()
            &&
            function_exists('current_theme_supports') && ! current_theme_supports('html5', 'script')
        ) {
            $this->type_attr = " type='text/javascript'";
        }

        /**
         * Copy the contents of existing $wp_scripts into the new one.
         * This is needed for numerous plug-ins that do not play nice.
         *
         * https://wordpress.stackexchange.com/a/284495/198117
         */
        if ($GLOBALS['wp_scripts'] instanceof WP_Scripts) {
            $missing_scripts = array_diff_key($GLOBALS['wp_scripts']->registered, $this->registered);
            foreach ($missing_scripts as $mscript) {
                $this->registered[$mscript->handle] = $mscript;
            }
        }
    }

    /**
     * Adapted from wp-includes/class.wp-scripts.php and added the
     * filter `wp_filterable_script_extra_tag`
     *
     * @param string $handle
     * @param bool $display
     *
     * @return bool|mixed|string|void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function print_extra_script($handle, $display = true)
    {
        $output = parent::print_extra_script($handle, false);

        if (! $output) {
            return $output;
        }

        $tag = wp_get_inline_script_tag($output, array('id' => "{$handle}-js-extra"));

        $tag = apply_filters('wp_filterable_script_extra_tag', $tag, $handle, $this->l10n_store[$handle] ?? []);

        if ($display) {
            echo $tag;
            return true;
        }

        return $tag;
    }

    /**
     * This method is exactly the same as the parent method except that
     * it stores the localization data in a class property before calling
     * the parent method.
     *
     * @param string $handle
     * @param string $object_name
     * @param array $l10n
     * @return void
     */
    public function localize($handle, $object_name, $l10n)
    {
        // Add the localization data to our store.
        if (!isset($this->l10n_store[$handle])) {
            $this->l10n_store[$handle] = [];
        }

        $this->l10n_store[$handle][$object_name] = $l10n;

        // Call the parent method to do the actual localization.
        return parent::localize($handle, $object_name, $l10n);
    }
}
