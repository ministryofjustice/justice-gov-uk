<?php

/*
 * A component to display an image
 *
 * Available variables:
 * - url: string The url for the image
 * - alt: string The alt text for the image
 * - srcset: string (optional) The srcset for the image, use wp_get
 * - sizes: string (optional) The sizes for the srcset attribute, use wp_get_attachment_image_sizes($id, $variant) to get this in WordPress
 *
 * Example usage:
 *   get_template_part('template-parts/common/image', null, [
 *     'url' => 'https://example.com/image.jpg',
 *     'alt' => 'An example image',
 *     'srcset' => 'https://example.com/image-1x.jpg 1x, https://example.com/image-2x.jpg 2x',
 *     'sizes' => '(max-width: 600px) 100vw, 600px',
 *   ]);
 */

defined('ABSPATH') || exit;

if (empty($args['url'])) {
    return;
}

$defaults = [
    'alt' => null,
    'srcset' => null,
    'sizes' => null,
];

$args = array_merge($defaults, $args);

?>

<div class="image">
    <img src="<?= esc_url($args['url']) ?>"
        <?= $args['alt'] ? 'alt="' . esc_attr($args['alt']) . '"' : '' ?>
        <?= $args['srcset'] ? 'srcset="' . esc_attr($args['srcset']) . '"' : '' ?>
        <?= $args['sizes'] ? 'sizes="' . esc_attr($args['sizes']) . '"' : '' ?> />
</div>
