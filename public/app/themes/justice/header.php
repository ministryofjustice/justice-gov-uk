<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) & !(IE 8)]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width"/>
    <link rel="profile" href="https://gmpg.org/xfn/11"/>
    <link rel="pingback" href="<?php echo esc_url(get_bloginfo('pingback_url')); ?>">
    <?php // Loads HTML5 JavaScript file to add support for HTML5 elements in older IE versions. ?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon-16x16.png">
    <link rel="manifest" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/site.webmanifest">
    <link rel="mask-icon" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/browserconfig.xml">
    <!--[if lt IE 9]>
<script src="<?php echo esc_url(get_template_directory_uri()); ?>/js/html5.js?ver=3.7.0" type="text/javascript"></script>
<![endif]-->
    <?php wp_head(); ?>
</head>
