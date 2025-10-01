<?php

defined('ABSPATH') || exit;

if (empty($args['image']['url'])) {
    return;
}

// If we have a title, ensure we have a tag and text
if (!empty($args['title']) && (!isset($args['title']['tag']) || !isset($args['title']['text']))) {
    return;
}

$defaults = [
    'direction' => 'left',
];

$args = array_merge($defaults, $args);

?>

<div class="image-with-text image-with-text--<?= esc_attr($args['direction']) ?>">

    <div class="image-with-text__image">
        <?php get_template_part('template-parts/common/image', null, [
            'url' => $args['image']['url'],
            'alt' => $args['image']['alt'] ?? null,
            'srcset' => $args['image']['srcset'] ?? null,
            'sizes' => $args['image']['sizes'] ?? null,
        ]); ?>
    </div>

    <div class="image-with-text__content">

        <?php if (!empty($args['title'])) : ?>
            <div class="image-with-text__title">

                <?php printf('<%s>', esc_attr($args['title']['tag'])); ?>
                    <?php if (!empty($args['title']['url'])) : ?>
                        <a href={{ title.url }}><?= esc_html($args['title']['text']) ?></a>
                    <?php else : ?>
                        <?= esc_html($args['title']['text']) ?>
                    <?php endif; ?>
                <?php printf('</%s>', esc_attr($args['title']['tag'])); ?>

            </div>
        <?php endif; ?>

        <?php get_template_part('template-parts/common/rich-text', null, [
            'content' => $args['content'] ?? '',
        ]); ?>
    </div>
</div>