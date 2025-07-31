<?php

use MOJ\Justice;
use Roots\WPConfig\Config;
use Timber\Timber;

defined('ABSPATH') || exit;

if (defined('WP_CLI') && WP_CLI) {
    require_once 'inc/commands.php';
}

if (Config::get('WP_OFFLOAD_MEDIA_PRESET') === 'minio') {
    require_once 'inc/amazon-s3-and-cloudfront-tweaks-for-minio.php';
}

require_once 'inc/admin.php';
require_once 'inc/admin-branding.php';
require_once 'inc/block-editor.php';
require_once 'inc/breadcrumbs.php';
require_once 'inc/commands.php';
require_once 'inc/content-quality/content-quality.php';
require_once 'inc/content.php';
require_once 'inc/core.php';
require_once 'inc/debug.php';
require_once 'inc/disable-comments.php';
require_once 'inc/disable-posts.php';
require_once 'inc/documents/documents.php';
require_once 'inc/dynamic-menu.php';
require_once 'inc/errors.php';
require_once 'inc/layout.php';
require_once 'inc/mail.php';
require_once 'inc/plugin-hacks.php';
require_once 'inc/post-meta/post-meta.php';
require_once 'inc/redirects.php';
require_once 'inc/search.php';
require_once 'inc/security.php';
require_once 'inc/simple-definition-list-blocks.php';
require_once 'inc/simple-guten-fields/simple-guten-fields.php';
require_once 'inc/sitemap.php';
require_once 'inc/taxonomies.php';
require_once 'inc/templates.php';
require_once 'inc/theme-assets.php';
require_once 'inc/theme.php';
require_once 'inc/utils.php';

// Initialise Timber (https://timber.github.io/docs/v2/)
Timber::init();
Timber::$dirname = [
    [
        'views'
    ],
];
// Create aliases for the frontend templates. These can be accessed with @components/component-name/component-name.html.twig
add_filter('timber/locations', function ($paths) {
    $paths['components'] = [WP_CONTENT_DIR . '/frontend/src/components'];
    $paths['layouts'] = [WP_CONTENT_DIR . '/frontend/src/layouts'];
    $paths['partials'] = [get_template_directory() . '/views/partials'];
    return $paths;
});

// Cache the twig templates (https://timber.github.io/docs/v2/guides/performance/)
add_filter('timber/twig/environment/options', function ($options) {
    $options['cache']       = true;
    // Enable auto_reload in the development environment
    $options['auto_reload'] = !!(getenv('WP_ENV') === 'development');
    return $options;
});

if (getenv('WP_ENV') === 'development') {
    $debug = new Justice\Debug();
    $debug->addHooks();
}

new Justice\Admin();
new Justice\AdminBranding();
new Justice\Commands();
new Justice\Comments();
new Justice\Content();
new Justice\ContentQuality();
new Justice\Core();
new Justice\Layout();
new Justice\Posts();
new Justice\Redirects();
new Justice\Security();
new Justice\SimpleGutenFields();
new Justice\SimpleDefinitionsListBlocks();
new Justice\Sitemap();
new Justice\ThemeAssets();
new Justice\Theme();

$block_editor = new Justice\BlockEditor();
$block_editor->addHooks();

$documents = new Justice\Documents();
$documents->addHooks();
$documents->removeHooks();

$post_meta = new Justice\PostMeta();
$post_meta->addHooks();

$taxonomies = new Justice\Taxonomies();
$taxonomies->addHooks();

$templates = new Justice\Templates();
$templates->addHooks();

$search = new Justice\Search();
$search->addHooks();

add_action('init', fn() => register_nav_menus([
    'header-menu' => __('Header Menu'),
    'footer-menu' => __('Footer Menu')
]));
