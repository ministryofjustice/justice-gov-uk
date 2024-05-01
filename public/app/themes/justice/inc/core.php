<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Actions and filters related to removing unused features from WordPress core.
 */

class Core
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks(): void
    {
        // Remove the welcome panel.
        add_action('admin_init', [$this, 'removeWelcomePanel']);
        // Remove default dashboard widgets.
        add_action('wp_dashboard_setup', [$this, 'removeDefaultDashboardWidgets']);
        // Disable remote block patterns. Avoids unnecessary transient entry in the database.
        add_filter('should_load_remote_block_patterns', '__return_false');
        // Avoids unnecessary transient entry in the database, by returning an empty array.
        add_filter('translations_api', fn () => []);
    }

    /**
     * Remove the welcome panel from the dashboard.
     *
     * @return void
     */

    public function removeWelcomePanel(): void
    {
        remove_action('welcome_panel', 'wp_welcome_panel');
    }

    /**
     * Removes various default dashboard widgets.
     *
     * Some of them create unnecessary transient entries in the database.
     * Others are only relevant to posts, and will likely never be used.
     *
     * @return void
     */

    public function removeDefaultDashboardWidgets(): void
    {
        // Transient entries.
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');      // Right Now/At a Glance
        remove_meta_box('dashboard_primary', 'dashboard', 'side');          // WordPress blog/events
        // Posts.
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');      // Quick Press/Quick Draft
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');       // Activity
    }
}
