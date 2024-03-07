<?php

use MOJ\Justice;
use Roots\WPConfig\Config;

if (!defined('ABSPATH')) {
    exit;
}

if (defined('WP_CLI') && WP_CLI) {
    require_once 'inc/commands.php';
}

if (Config::get('WP_OFFLOAD_MEDIA_PRESET') === 'minio') {
    require_once 'inc/amazon-s3-and-cloudfront-tweaks-for-minio.php';
}

require_once 'inc/admin.php';
require_once 'inc/block-editor.php';
require_once 'inc/breadcrumbs.php';
require_once 'inc/debug.php';
require_once 'inc/disable-comments.php';
require_once 'inc/dynamic-menu.php';
require_once 'inc/errors.php';
require_once 'inc/layout.php';
require_once 'inc/mail.php';
require_once 'inc/post-meta/post-meta.php';
require_once 'inc/simple-guten-fields/simple-guten-fields.php';
require_once 'inc/taxonomies.php';

if (getenv('WP_ENV') === 'development') {
    $debug = new Justice\Debug();
    $debug->registerHooks();
}

new Justice\Admin();
new Justice\Comments();
new Justice\Layout();
new Justice\SimpleGutenFields();

$block_editor = new Justice\BlockEditor();
$block_editor->registerHooks();

$post_meta = new Justice\PostMeta();
$post_meta->registerHooks();

$taxonomies = new Justice\Taxonomies();
$taxonomies->registerHooks();

add_action('wp_enqueue_scripts', fn() => wp_enqueue_style('style-name', get_stylesheet_uri()));

add_action('wp_enqueue_scripts', fn() => wp_enqueue_style('justice-styles', get_template_directory_uri() . '/dist/app.min.css'));

add_editor_style();

add_action('init', fn() => register_nav_menus([
    'header-menu' => __('Header Menu'),
    'footer-menu' => __('Footer Menu')
]));
