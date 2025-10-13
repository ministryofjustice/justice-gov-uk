<?php

/*
 * The site header
 *
 * Available variables:
 * - show_search: boolean Should the search bar be visible
 * - links: array An array of links to be displayed in the nav component
 *   - url: string The url for the link
 *   - label: string The label for the link
 *   - new_tab: boolean Will this link open in a new tab?
 *   - new_tab_visually_hidden: boolean Will the (open in a new tab) text be shown to all users or screen readers only?
 *   - active: boolean Is this link the current page?
 *   - on_click: string JavaScript to run when the link is clicked
 * - search_form: array Parameters to be passed to the text input form component, if it's to be displayed
 *   - id: string A unique id for the form
 *   - action: string The form action
 *   - input: array Parameters to be passed to the text input component
 *     - id: string A unique id for the input
 *     - name: string The name of the input
 *     - label: string The label text for the input
 *   - button: array Parameters to be passed to the button component
 *     - text: string The button text
 *
 * Example usage:
 *   get_template_part('template-parts/common/header', null, [
 *     'show_search' => true,
 *     'links' => [
 *       [
 *         'url' => '#',
 *         'label' => 'Courts',
 *         'new_tab' => true,
 *       ],
 *       [
 *         'active' => true,
 *         'url' => '#',
 *         'label' => 'Procedure rules',
 *       ],
 *       [
 *         'url' => '#',
 *        'label' => 'Offenders',
 *       ],
 *     ],
 *    'search_form' => [
 *      'id' => 'search-bar-header',
 *      'action' => '#',
 *      'input' => [
 *        'id' => 'search-bar-header-input',
 *        'label_hidden' => true,
 *        'label' => 'Search the Justice UK website',
 *        'name' => 's',
 *      ],
 *      'button' => [
 *        'text' => 'Search',
 *      ],
 *    ],
 * ]);
 */

defined('ABSPATH') || exit;

?>

<header class="header">
    <div class="header__container">
        <a class="header__home" href="/">
            <span class="header__home-label visually-hidden">
                Justice UK - Homepage
            </span>
            <div class="header__brand">
                <div class="header__logo"></div>
                <div class="header__logotype"></div>
            </div>
        </a>
        <?php if ($args['show_search']) : ?>
            <div class="header__search">
                <?php get_template_part('template-parts/common/text-input-form', null, $args['search_form']); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="header__nav">
        <?php get_template_part('template-parts/nav/navigation-main', null, [
            'links' => $args['links'] ?? [],
        ]); ?>
    </div>
</header>
