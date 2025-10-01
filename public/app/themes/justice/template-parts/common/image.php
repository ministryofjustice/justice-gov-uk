<?php

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
