<?php 

defined('ABSPATH') || exit;

$defaults = [
    'title' => '',
    'breadcrumbs' => [],
    'eyebrow_text' => '',
];

$args = array_merge($defaults, $args);

?>

<div class="hero">
    <?php if (!empty($args['breadcrumbs'])) : ?>
        <div class="hero__breadcrumbs">
            <?php
            get_template_part('template-parts/nav/breadcrumbs.v2', null, [
                'links' => $args['breadcrumbs'],
            ]);
            ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($args['eyebrow_text'])) : ?>
        <hgroup>
            <h1 class="hero__title">
                <p class="hero__caption caption"><?= esc_html($args['eyebrow_text']) ?></p>
                <?= esc_html($args['title']) ?>
            </h1>
        </hgroup>
    <?php else : ?>
        <h1 class="hero__title"><?= esc_html($args['title']) ?></h1>
    <?php endif; ?>
</div>