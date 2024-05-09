<?php

namespace MOJ\Justice;

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
}
