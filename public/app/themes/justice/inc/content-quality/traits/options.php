<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * Add an options page for content quality settings.
 */

trait OptionsPage
{
    public function addOptionsPage(): void
    {
        // Add a submenu page under the Pages menu.
        add_submenu_page(
            'edit.php?post_type=page',
            __('Content Quality Settings', 'justice'),
            __('Content Quality Settings', 'justice'),
            'edit_pages',
            'content-quality-settings',
            [$this, 'renderOptionsPage']
        );
    }

    public function renderOptionsPage(): void
    {
        get_template_part('inc/content-quality/traits/options-template');
    }
}
