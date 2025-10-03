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
    <link rel="icon" type="image/png" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon.svg" />
    <link rel="shortcut icon" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Justice UK" />
    <link rel="manifest" href="<?php echo esc_url(get_template_directory_uri()); ?>/dist/img/icon/fav/site.webmanifest" />
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
                <a href="/" accesskey="1">Home</a><a href="#skip_nav" accesskey="s">&nbsp;</a>
            </div>
            <?php
            // TODO: replace hardcoded menu with dynamic menu.
            // wp_nav_menu([
            //     'theme_location' => 'header-menu',
            //     'container' => 'nav',
            //     'container_class' => 'menu-top',
            //     'fallback_cb' => false
            // ]);

            $menu_items = [
                [
                    'title' => 'Courts',
                    'url' => 'https://www.gov.uk/government/organisations/hm-courts-and-tribunals-service',
                    'onclick' => "gtag && gtag('event', 'page_view', { page_title: 'Courts', page_location: '" . home_url('/courts') . "' });"
                ],
                [
                    'title' => 'Procedure rules',
                    'url' => home_url('/courts/procedure-rules'),
                    'active' => str_starts_with(get_permalink(), home_url('/courts/procedure-rules')),
                    'onclick' => null
                ],
                [
                    'title' => 'Offenders',
                    'url' => 'https://www.gov.uk/government/organisations/hm-prison-and-probation-service',
                    'onclick' => "gtag && gtag('event', 'page_view', { page_title: 'Offenders', page_location: '" . home_url('/offenders') . "' });"
                ]
            ];
            ?>
            <nav>             
                <ul class="menu-top">
                    <?php foreach ($menu_items as $item) : ?>
                        <li <?php echo !empty($item['active']) ? 'class="active"' : '' ?>>
                            <a href="<?php echo $item['url']; ?>" onclick="<?= $item['onclick'] ?? '' ?>">
                                <?= $item['title'] ?>
                            </a>
                            <div class="finish"></div>
                            <span></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
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

