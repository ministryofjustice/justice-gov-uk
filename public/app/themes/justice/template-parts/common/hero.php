<?php

/*
 * A hero component that can also display breadcrumbs and an eyebrow heading, optionally
 *
 * Available variables:
 *  - title: string The page title (h1)
 * - eyebrow_text: string An eyebrow heading (optional)
 * - breadcrumbs: array An array of links to be displayed in the breadcrumb component (optional)
 *   - url: string The url for the link
 *   - label: string The label for the link
 *
 * Example usage:
 *   get_template_part('template-parts/common/hero', null, [
 *     'eyebrow_text' => 'Procedure rules',
 *     'title' => 'Family procedure rules',
 *     'breadcrumbs' => [
 *       [
 *         'url' => '#',
 *         'label' => 'Home',
 *       ],
 *       [
 *         'url' => '#',
 *         'label' => 'Procedure rules',
 *       ],
 *       [
 *         'url' => '#',
 *         'label' => 'Family',
 *       ],
 *     ],
 *   ]);
 */

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
