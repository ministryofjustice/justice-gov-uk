<?php

/*
 * The sidebar navigation for the site
 *
 * Available variables:
 *   - id: string The ID of the navigation
 *   - title: string The title of the navigation
 *   - links: array An array of links to be displayed
 *     - url: string The URL for the link
 *     - label: string The label for the link
 *     - expanded: boolean Whether the link is expanded
 *     - children: array An array of child links
 *     - active: boolean Whether the link is active
 *
 * Example usage:
 *  get_template_part('template-parts/nav/navigation-secondary', null, [
 *    'id' => 'navigation-secondary',
 *    'title' => 'Navigation',
 *    'links' => [
 *      [
 *        'url' => '#',
 *        'label' => 'Link 1',
 *        'expanded' => true,
 *        'children' => [
 *          [
 *            'url' => '#',
 *            'label' => 'Child Link 1',
 *            'active' => true,
 *          ],
 *          [
 *            'url' => '#',
 *            'label' => 'Child Link 2',
 *          ],
 *        ],
 *      ],
 *    ]
 *  ]);
 */

defined('ABSPATH') || exit;

?>
<div class="navigation-secondary">
    <div class="navigation-secondary__button-wrapper">
        <button class="navigation-secondary__button navigation-secondary__button--nav" aria-expanded="false"
            aria-controls="navigation-secondary">
            <span class="visually-hidden">Open secondary </span>menu
        </button>
    </div>
    <div class="navigation-secondary__heading" aria-hidden="true"><?= esc_html($args['title']) ?></div>
    <nav id="navigation-secondary" class="navigation-secondary__nav" aria-label="Secondary">
        <?php get_template_part('template-parts/nav/navigation-secondary-inner', null, [
            'items' => $args['links'],
            'menu_level' => 0,
            'menu_id' => 0,
            'article_id' => $args['id'],
            'expanded' => false,
        ]); ?>
    </nav>
</div>
