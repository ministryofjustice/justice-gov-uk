<?php

use MOJ\Justice;
use Roots\WPConfig\Config;

defined('ABSPATH') || exit;

if (defined('WP_CLI') && WP_CLI) {
    require_once 'inc/commands.php';
}

if (Config::get('WP_OFFLOAD_MEDIA_PRESET') === 'minio') {
    require_once 'inc/amazon-s3-and-cloudfront-tweaks-for-minio.php';
}

require_once 'inc/acf/acf.php';
require_once 'inc/admin.php';
require_once 'inc/admin-branding.php';
require_once 'inc/amazon-s3-and-cloudfront-assets.php';
require_once 'inc/amazon-s3-and-cloudfront-tweaks.php';
require_once 'inc/block-editor.php';
require_once 'inc/breadcrumbs.php';
require_once 'inc/commands.php';
require_once 'inc/content-quality/commands.php';
require_once 'inc/content-quality/content-quality.php';
require_once 'inc/content-links.php';
require_once 'inc/content.php';
require_once 'inc/core.php';
require_once 'inc/debug.php';
require_once 'inc/disable-comments.php';
require_once 'inc/disable-posts.php';
require_once 'inc/documents/documents.php';
require_once 'inc/dynamic-menu.php';
require_once 'inc/errors.php';
require_once 'inc/header.php';
require_once 'inc/layout.php';
require_once 'inc/mail.php';
require_once 'inc/navigation-secondary.php';
require_once 'inc/nginx-cache.php';
require_once 'inc/plugin-hacks.php';
require_once 'inc/post-meta/post-meta.php';
require_once 'inc/redirects.php';
require_once 'inc/search.php';
require_once 'inc/security.php';
require_once 'inc/simple-definition-list-blocks.php';
require_once 'inc/sitemap.php';
require_once 'inc/taxonomies.php';
require_once 'inc/theme-assets.php';
require_once 'inc/theme.php';
require_once 'inc/updates.php';
require_once 'inc/utils.php';
require_once 'inc/wp-script-localization.php';
require_once 'inc/wp-scripts.php';

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
new Justice\Documents();
new Justice\Layout();
new Justice\NginxCache();
new Justice\Posts();
new Justice\Redirects();
new Justice\Security();
new Justice\SimpleDefinitionsListBlocks();
new Justice\Sitemap();
new Justice\ThemeAssets();
new Justice\Theme();
new Justice\WpScriptLocalization();

$block_editor = new Justice\BlockEditor();
$block_editor->addHooks();

(new Justice\NavigationSecondary())->addHooks();

$post_meta = new Justice\PostMeta();
$post_meta->addHooks();

(new Justice\Search())->addHooks();

$taxonomies = new Justice\Taxonomies();
$taxonomies->addHooks();

add_action('init', fn() => register_nav_menus([
    'header-menu' => __('Header Menu'),
    'footer-menu' => __('Footer Menu')
]));

/**
 * Temporarily set X-Canary cookie based on query string.
 *
 * This will allow our team to share a link like:
 * www.justice.gov.uk?moj_version=preview with the team and stakeholders.
 * This will force Cloud Platform ingress to serve the site from the
 * hale-platform-* ingress.
 *
 * The cookie can be deleted with: www.justice.gov.uk?moj_version=reset
 *
 * The legacy version of the site can be forced with:
 * www.justice.gov.uk?moj_version=legacy
 */
add_action('init', function () {
    if (empty($_GET['moj_version'])) {
        return;
    }

    switch ($_GET['moj_version']) {
        case 'preview':
            setcookie("X-Canary", 'always', 60 * 60 * 24 * 30 + time(), COOKIEPATH, COOKIE_DOMAIN);
            break;
        case 'legacy':
            setcookie("X-Canary", 'never', 60 * 60 * 24 * 30 + time(), COOKIEPATH, COOKIE_DOMAIN);
            break;
        case 'reset':
            setcookie("X-Canary", '', time() - 1000, COOKIEPATH, COOKIE_DOMAIN);
            break;
    }
});
