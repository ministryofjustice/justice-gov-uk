<?php

/*
 * A component to display breadcrumbs
 *
 * Available variables:
 *   - links: array An array of links to be displayed
 *      - url: string The url for the link
 *      - label: string The label for the link
 *
 * Example usage:
 *   get_template_part('nav/breadcrumbs', null, [
 *     'links' => [
 *       [
 *         'url' => '#',
 *         'label' => 'Home'
 *       ],
 *       [
 *         'url' => '#',
 *         'label' => 'Procedure rules'
 *       ],
 *       [
 *         'url' => '#',
 *         'label' => 'Family'
 *        ],
 *     ]
 *   ]);
 */

defined('ABSPATH') || exit;

if (empty($args['links'])) {
    return;
}

?>

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <ul class="breadcrumbs__list">
        <?php foreach ($args['links'] as $i => $link) : ?>
            <li class="breadcrumbs__item">
                <?php if ($i + 1 < sizeof($args['links'])) : ?>
                    <a class="breadcrumbs__link" href="<?= esc_url($link['url']) ?>"><?= esc_html($link['label']) ?></a>
                <?php else : ?>
                    <a class="breadcrumbs__link disabled" role="link" aria-disabled="true"
                    aria-current="page"><?= esc_html($link['label']) ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
