<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Comments
 * Actions and filters related to WordPress comments.
 */
class Comments
{

    /**
     * Constructor.
     */

    public function __construct()
    {
        $this->registerHooks();
    }

    /**
     * Static function must be called after require within functions.php
     * This will setup all action and filter hooks related to comments.
     */

    public function registerHooks()
    {

        // Admin actions.
        add_action('admin_init', array($this, 'disable_comments_post_types_support'));
        add_action('admin_init', array($this, 'disable_comments_dashboard'));
        add_action('admin_init', array($this, 'disable_comments_admin_menu_redirect'));
        add_action('admin_menu', array($this, 'disable_comments_admin_menu'));
        add_action('wp_before_admin_bar_render', array($this, 'admin_bar_render'));
        add_filter('the_comments', '__return_empty_array');
        add_filter('feed_links_show_comments_feed', '__return_false');
        
        // Frontend.
        add_action('init', array($this, 'disable_comments_admin_bar'));
        add_filter('get_comments_number', '__return_zero');

        // Close comments on the front-end.
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);

        // Hide existing comments.
        add_filter('comments_array', '__return_empty_array', 10, 2);
    }

    /** Disable support for comments and trackbacks in post types. */
    public function disable_comments_post_types_support()
    {
        $post_types = get_post_types();
        foreach ($post_types as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    /** Remove comments page in menu. */
    public function disable_comments_admin_menu()
    {
        remove_menu_page('edit-comments.php');
        remove_submenu_page('options-general.php', 'options-discussion.php');
    }

    /** Redirect any user trying to access comments page. */
    public function disable_comments_admin_menu_redirect()
    {
        global $pagenow;
        if (in_array($pagenow, ['edit-comments.php','options-discussion.php' ])) {
            wp_safe_redirect(admin_url());
            exit;
        }
    }


    /** Remove comments metabox from dashboard. */
    public function disable_comments_dashboard()
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }

    /**  Remove comments links from admin bar. */
    public function disable_comments_admin_bar()
    {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    }

    /**  Remove comments links from admin bar. */
    public function admin_bar_render()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    }
}
