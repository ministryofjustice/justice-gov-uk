<?php

use MOJ\Justice;

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
require_once 'inc/mail.php';
require_once 'src/components/post-meta/post-meta.php';

$post_meta = new Justice\PostMeta();
$post_meta->registerHooks();

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

add_action('enqueue_block_editor_assets', function () {

    $dir = __DIR__;

    $script_asset_path = "$dir/dist/block-editor.min.asset.php";
    if (! file_exists($script_asset_path)) {
        throw new Error(
            'You need to run `npm start` or `npm run build` for the "create-block/simple-guten-fields" block first.'
        );
    }

    $script_asset = require($script_asset_path);

    wp_register_script(
        'justice-block-editor',
        get_template_directory_uri() . '/dist/block-editor.min.js',
        $script_asset['dependencies'],
        $script_asset['version']
    );

    $post_meta = new Justice\PostMeta();
    $post_meta->localize();

    wp_enqueue_script('justice-block-editor');
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
