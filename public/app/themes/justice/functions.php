<?php

if (!defined('ABSPATH')) {
    exit;
}

if (defined('WP_CLI') && WP_CLI) {
    require_once 'inc/commands.php';
}

if (defined('WP_OFFLOAD_MEDIA_PRESET') && WP_OFFLOAD_MEDIA_PRESET === 'minio') {
    require_once 'inc/amazon-s3-and-cloudfront-tweaks-for-minio.php';
}

require_once 'inc/breadcrumbs.php';
require_once 'inc/dynamic-menu.php';
require_once 'inc/layout.php';
require_once 'inc/meta.php';

add_action('wp_enqueue_scripts', fn() => wp_enqueue_style('style-name', get_stylesheet_uri()));

add_action('wp_enqueue_scripts', fn() => wp_enqueue_style('justice-styles', get_template_directory_uri() . '/dist/app.min.css'));

add_editor_style();


add_action('init', fn() => register_nav_menus([
    'header-menu' => __('Header Menu'),
    'footer-menu' => __('Footer Menu')
]));

add_action('wp_before_admin_bar_render', function () {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('customize');
});

add_action(
    'admin_enqueue_scripts',
    function () {
        wp_enqueue_style(
            'justice-admin-style',
            get_template_directory_uri() . '/dist/css/wp-admin-override.css'
        );
    }
);


add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script('justice-admin', get_template_directory_uri() . '/dist/admin.min.js', [], false, true);
});

// B R E A D C R U M B S //
function justice_crumbs()
{
    global $post;
    /* Change according to your needs */
    $show_current = 1;
    $delimiter = '»';
    $home_url = 'Home';
    $before_wrap = '<li class="current">';
    $after_wrap = '</li>';

    $home_url = get_bloginfo('url');

    if (is_home() || is_front_page()) {
        return;
    }

    /* Proceed with showing the breadcrumbs */
    $breadcrumbs = '<ol id="crumbs" itemscope itemtype="https://schema.org/BreadcrumbList">';

    $breadcrumbs .= '<li itemprop="itemListElement" itemtype="https://schema.org/ListItem"><a target="_blank" href="' . $home_url . '">' . $home_url . '</a></li>';

    /* Build breadcrumbs here */

    $breadcrumbs .= '</ol>';
    echo $breadcrumbs;
}
