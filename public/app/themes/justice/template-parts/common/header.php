<?php 

defined('ABSPATH') || exit;

?>

<header class="header">
    <div class="header__container">
        <a class="header__home" href="/">
            <span class="header__home-label visually-hidden">
                Justice UK - Homepage
            </span>
            <div class="header__brand">
                <div class="header__logo"></div>
                <div class="header__logotype"></div>
            </div>
        </a>
        <?php if ($args['show_search']) : ?>
            <div class="header__search">
                <?php get_template_part('template-parts/common/text-input-form', null, $args['search_form']); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="header__nav">
        <?php get_template_part('template-parts/nav/navigation-main', null, [
            'links' => $args['links'] ?? [],
        ]); ?>
    </div>
</header>