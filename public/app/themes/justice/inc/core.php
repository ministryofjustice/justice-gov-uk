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
        // Remove Posts from side menu.
        add_action('admin_menu', [$this, 'removePostFromMenu']);
        // Remove +New post in top Admin Menu Bar
        add_action('admin_bar_menu', [$this, 'removePostFromMenuBar'], 999);

        // add_filter('user_has_cap', [$this, 'dumpUserCaps'], 999, 4 );

        // add_action('init',  [$this, 'disableCapabilities']);


        // Redirect admin edit post to page
        // add_action('parse_query', [$this, 'redirectPostToPage']);
    }

    /**
     * Remove capabilities from editors.
     *
     * Call the function when your plugin/theme is activated.
     */
    // function disableCapabilities()
    // {

    //     // Get the role object.
    //     $editor = get_role('editor');

    //     $user = wp_get_current_user();

    //     // Loop over user roles
    //     foreach ($user->roles as $role) {
    //         error_log('role: ' . $role);
    //     }

    //     // A list of capabilities to remove from editors.
    //     $caps = array(
    //         'edit_posts',
    //         'edit_others_posts',
    //         'edit_published_posts',
    //         'edit_private_posts',
    //         'read_private_posts',
    //         'delete_posts',
    //         'delete_others_posts',
    //         'delete_private_posts',
    //         'delete_published_posts',
    //         'publish_posts',
    //     );

    //     foreach ($caps as $cap) {

    //         // Remove the capability.
    //         $editor->remove_cap($cap);
    //     }
    // }

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

    public function removePostFromMenu()
    {
        remove_menu_page('edit.php');
    }


    public function removePostFromMenuBar($wp_admin_bar)
    {
        $wp_admin_bar->remove_node('new-post');
    }

    // if editing post post type, redirect to page post type
    public function redirectPostToPage($query)
    {
        global $pagenow;

        // Get post_type from url query


        error_log('hi');
        error_log('is admin: ' . is_admin());
        error_log('pagenow: ' . $pagenow);
        error_log('query: ' . print_r($query, true));

        if (is_admin() && $pagenow == 'post-new.php' && $query->query["post_type"] == "post") {
            wp_redirect(admin_url('post-new.php?post_type=page'));
            exit;
        }

        return $query;
    }
}
