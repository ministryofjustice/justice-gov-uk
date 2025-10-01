<?php 

defined('ABSPATH') || exit;

?>
<div class="navigation-secondary">
    <div class="navigation-secondary__button-wrapper">
        <button class="navigation-secondary__button navigation-secondary__button--nav" aria-expanded="false"
            aria-controls="navigation-secondary">
            <span class="visually-hidden">Open secondary </span>menu
        </button>
    </div>
    <div class="navigation-secondary__heading" aria-hidden="true"><?= esc_html($args['title']) ?></div>
    <nav id="navigation-secondary" class="navigation-secondary__nav" aria-label="Secondary">
        <?php // error_log(print_r($args['links'], true)); ?>
        <?php get_template_part('template-parts/nav/navigation-secondary-inner', null, [
            'items' => $args['links'],
            'menu_level' => 0,
            'menu_id' => 0,
            'article_id' => $args['id'],
            'expanded' => false,
        ]); ?>
    </nav>
</div>
