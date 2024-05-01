<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Posts
 * Actions and filters related to WordPress post type.
 */
class Posts
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
        // Remove Posts from side menu.
        add_action('admin_menu', [$this, 'removePostFromMenu']);
        // Remove +New post in top Admin Menu Bar
        add_action('admin_bar_menu', [$this, 'removePostFromMenuBar'], 999);
        // Redirects to page if someone tries to save a post.
        add_action('save_post', [$this, 'preventSavingPostPostType']);
    }

    /**
     * Remove Posts from side menu.
     *
     * @return void
     */

    public function removePostFromMenu(): void
    {
        remove_menu_page('edit.php');
    }


    /**
     * Remove the 'New Post' link from the admin bar.
     *
     * @param WP_Admin_Bar $wp_admin_bar
     * @return void
     */
    public function removePostFromMenuBar($wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('new-post');
    }


    /**
     * Prevents saving if the post type is 'post'.
     *
     * This function also redirects to a page post type
     * if someone tries to create a new post.
     *
     * @param int $post_id
     * @return void
     */

    public function preventSavingPostPostType(int $post_id): void
    {
        // If this is a revision, get real post ID.
        $parent_id = wp_is_post_revision($post_id);

        if (false !== $parent_id) {
            $post_id = $parent_id;
        }

        if (get_post_type($post_id) === 'post') {
            wp_redirect(admin_url('post-new.php?post_type=page'));
            exit;
        }
    }
}
