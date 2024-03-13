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
        $this->addHooks();
    }

    /**
     * Static function must be called after require within functions.php
     * This will setup all action and filter hooks related to comments.
     */

    public function addHooks()
    {

        // Admin actions.
        add_action('admin_init', array($this, 'removePostTypesSupport'));
        add_action('admin_init', array($this, 'removeCommentsMetaBox'));
        add_action('admin_init', array($this, 'adminPagesRedirect'));
        add_action('admin_menu', array($this, 'removeAdminMenus'));
        add_action('wp_before_admin_bar_render', array($this, 'adminBarRender'));
        add_filter('the_comments', '__return_empty_array');
        add_filter('feed_links_show_comments_feed', '__return_false');
        
        // Frontend.
        add_action('init', array($this, 'removeMenuFromAdminBar'));
        add_filter('get_comments_number', '__return_zero');

        // Close comments on the front-end.
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);

        // Hide existing comments.
        add_filter('comments_array', '__return_empty_array', 10, 2);

        // Set default state on posts.
        add_filter('get_default_comment_status', fn() => 'closed');
    }

    /** Disable support for comments and trackbacks in post types. */
    public function removePostTypesSupport()
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
    public function removeAdminMenus()
    {
        remove_menu_page('edit-comments.php');
        remove_submenu_page('options-general.php', 'options-discussion.php');
    }

    /** Redirect any user trying to access comments page. */
    public function adminPagesRedirect()
    {
        global $pagenow;
        if (in_array($pagenow, ['edit-comments.php','options-discussion.php' ])) {
            wp_safe_redirect(admin_url());
            exit;
        }
    }


    /** Remove comments meta box from dashboard. */
    public function removeCommentsMetaBox()
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }

    /**  Remove comments links from admin bar. */
    public function removeMenuFromAdminBar()
    {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    }

    /**  Remove comments links from admin bar. */
    public function adminBarRender()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    }
}
