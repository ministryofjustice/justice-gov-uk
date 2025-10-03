<?php

defined('ABSPATH') || exit;

if (empty($args['url'])) {
    return;
}

$defaults = [
    'new_tab' => false,
    'new_tab_visually_hidden' => true,
    'on_click' => null,
    'aria_current' => null,
    'external' => false,
];

$args = array_merge($defaults, $args);

?><a
    href="<?= esc_attr($args['url']) ?>"
    class="link<?= $args['new_tab'] ? ' link--new-tab' : '' ?><?= $args['external'] ? ' link--external' : '' ?>"
    <?= $args['new_tab'] ? ' target="_blank" rel="noreferrer noopener"' : '' ?>
    <?= $args['on_click'] ? " onclick='" . esc_attr($args['on_click']) . "'" : '' ?>
    <?= $args['aria_current'] ? ' aria-current="' . esc_attr($args['aria_current']) . '"' : '' ?>
><?php
    // Use php tags immediately inside the a tag, to avoid formatting issues.
    echo esc_html($args['label']);

if ($args['new_tab']) {
    printf(
        '<span class="link__new-tab-suffix %s"> (opens in a new tab)</span>',
        $args['new_tab_visually_hidden'] ? 'visually-hidden' : ''
    );
}
?></a>