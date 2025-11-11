<?php

defined('ABSPATH') || exit;

use Roots\WPConfig\Config;
use MOJ\Justice\Header;

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width"/>
    <link rel="icon" type="image/png" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon.svg" />
    <link rel="shortcut icon" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Justice UK" />
    <?php // Prevent webmanifest from being served by the CDN. 
        global $moj_skip_next_cdn_rewrite;
        $moj_skip_next_cdn_rewrite = true;
    ?>
    <link rel="manifest" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/site.webmanifest" />
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php

wp_body_open();

if (Config::get('FRONTEND_VERSION') === 1) {
    require get_template_directory() . '/header.v1.php';
    return;
}

// skip link for main content
get_template_part('template-parts/nav/skip-link', null, [
    'article_id' => '#main-page-content',
]);

?>

<div class="page-wrapper">
    <div class="header-wrapper">

<?php

get_template_part('template-parts/common/header', null, [
    'show_search' => Header::showSearch(),
    'search_form' => Header::FORM_ARGS,
    'links' => Header::getLinks(),
]);

?>

    </div>
    <main>
