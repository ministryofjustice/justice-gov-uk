<?php

namespace MOJ\Justice;

use Roots\WPConfig\Config;

defined('ABSPATH') || exit;

/**
 * A php class related to theme support.
 */

class Theme
{
    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks(): void
    {
        add_action('after_setup_theme', [$this, 'addThemeSupport']);
        add_filter('site_transient_update_themes', [$this, 'disableThemeUpdateNotification']);
    }


    /**
     * Add theme support for title-tag.
     *
     * @see https://developer.wordpress.org/reference/functions/add_theme_support/
     *
     * @return void
     */
    public function addThemeSupport(): void
    {
        add_theme_support('title-tag');
    }


    /**
     * Disable update notifications for the Justice Theme.
     *
     * The Justice theme shares a name with a published theme at a higher version so Wordpress reports that our theme needs updating.
     * This will prevent the warning from showing.
     *
     * @param mixed $transient A transient containing the pending theme update information
     *
     * @return mixed The unmodified transient or the updated transient if it's an object
     */
    public function disableThemeUpdateNotification($transient): mixed
    {
        if (isset($transient) && is_object($transient)) {
            unset($transient->response['justice']);
        }
        return $transient;
    }
}
