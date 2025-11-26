<?php

/*
 * The main navigation for the site
 *
 * Available variables:
 *  - links: array An array of links to be displayed
 *    - new_tab: boolean Whether the link will open in a new tab
 *    - active: boolean Whether the user is currently on the linked page
 *    - url: string The url for the link
 *    - label: string The label for the link
 *
 * Example usage:
 *   get_template_part('template-parts/nav/navigation-main', null, [
 *     'links' => [
 *       [
 *         'new_tab' => true,
 *         'active' => true,
 *         'url' => 'https://google.com',
 *         'label' => 'Click here'
 *       ],
 *       [
 *         'url' => '#',
 *         'label' => 'Click here'
 *       ]
 *     ]
 *   ]);
 */

defined('ABSPATH') || exit;

if (empty($args['links'])) {
    return;
}

?>

<nav id="navigation-main" class="navigation-main" aria-label="Primary">
    <div class="navigation-main__container">
        <ul class="navigation-main__list">
            <?php foreach ($args['links'] as $link) : ?>
                <li class="navigation-main__link<?= !empty($link['active']) ? ' navigation-main__link--active' : ''; ?>">
                    <?php
                    get_template_part(
                        'template-parts/common/link',
                        null,
                        [
                            'new_tab' => $link['new_tab'] ?? false,
                            'new_tab_visually_hidden' => $link['new_tab_visually_hidden'] ?? true,
                            'url' => $link['url'],
                            'label' => $link['label'],
                            'aria_current' => ($link['active'] ?? false) ? 'page' : null,
                        ]
                    )
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>
