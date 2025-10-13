<?php

/*
 * A component to display an image next to some text
 *
 * Available variables:
 * - direction: string (left|right) Which side should the image be on
 * - title: array An array with 'text', 'tag', and optionally 'url'
 *   - text: string The title text
 *   - tag: string The HTML tag for the title (defaults to h2)
 *   - url: string (optional) Make the title a link
 * - content: string The text content
 * - image: array An array with 'url', 'alt', 'srcset', and 'sizes'
 *   - url: string The url for the image
 *   - alt: string The alt text for the image
 *
 * Example usage:
 *   get_template_part('template-parts/common/image-with-text', null, [
 *     'direction' => 'left',
 *     'title' => [
 *         'text' => 'Welcome',
 *         'tag' => 'h1',
 *         'url' => '#',
 *     ],
 *     'content' => 'Lorem ipsum',
 *     'image' => [
 *         'url' => '#',
 *         'alt' => 'A sunset',
 *     ],
 *   ]);
 */

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
