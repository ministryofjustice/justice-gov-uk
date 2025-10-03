<?php
/*
 * A list of links to navigate the page content using anchor links provided
 *
 * Available variables:
 * - links: array An array of links to be displayed
 *     - url: string The url for the link
 *     - label: string The label for the link
 *
 * Example usage:
 *   get_template_part('template-parts/nav/navigation-sections', null, [
 *     'links' => [
 *       [
 *         'label' => '1-10',
 *         'url' => '#',
 *       ],
 *       [
 *         'label' => '11-20',
 *         'url' => '#',
 *       ],
 *       [
 *         'label' => '21-30',
 *         'url' => '#',
 *       ],
 *     ],
 *   ]);
 */

if (empty($args['links']) || !is_array($args['links'])) {
    return;
}
?>

<nav id="navigation-sections" class="navigation-sections" aria-label="Page sections">
    <ul class="navigation-sections__list">
        <?php foreach ($args['links'] as $link) : ?>
            <li class="navigation-sections__list-item">
                <a href="<?= esc_url($link['url'] ?? '') ?>"><span class="visually-hidden">Scroll to section: </span><?= esc_html($link['label'] ?? '') ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
