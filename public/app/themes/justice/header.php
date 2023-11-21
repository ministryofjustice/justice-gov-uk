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
    <title><?php wp_title('|', true, 'right'); ?></title>
    <link rel="profile" href="https://gmpg.org/xfn/11"/>
    <link rel="pingback" href="<?php echo esc_url(get_bloginfo('pingback_url')); ?>">
    <?php // Loads HTML5 JavaScript file to add support for HTML5 elements in older IE versions. ?>
    <!--[if lt IE 9]>
<script src="<?php echo esc_url(get_template_directory_uri()); ?>/js/html5.js?ver=3.7.0" type="text/javascript"></script>
<![endif]-->
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="header-wrapper">
    <div class="container-wrapper">
        <div id="header">
            <a name="top"></a><a name="pagetop"></a>
            <ul id="links-top">

                <li class="device-only">.</li>
            </ul>
            <div id="logo">
                <a href="/" accesskey="1">Home</a><a href="#skip_nav" style="display:none;" accesskey="s">&nbsp;</a>
            </div>
            <?php
            wp_nav_menu([
                'theme_location' => 'header-menu',
                'container' => 'nav',
                'container_class' => 'menu-top'
            ]);
            ?>
            <div id="search-top">
                <form action="/">
                    <label for="searchbox-top">Search</label>
                    <input type="text" id="searchbox-top" name="s" accesskey="4" class="ui-autocomplete-input"
                           autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true">
                    <input class="go-btn" type="submit" value="Search">
                </form>
            </div>
        </div>
    </div>
</div>
<div id="section-sub-wrapper">
    <div class="container-wrapper">
        <div id="section-sub"></div>
    </div>
</div>
<div class="h-line"></div>

