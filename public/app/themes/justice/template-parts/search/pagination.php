<?php

/*
 * A pager for lists of results, e.g. search
 *
 * Available variables:
 * - previous_url: string The url to go back a page
 * - next_url: string The url to go forwards
 * - pages: array An array of page links to be displayed
 *     - title: string The title of the page (usually a number)
 *     - url: string The link to go to that page
 *     - current: boolean Whether this is the current page
 *
 * Example:
 *   get_template_part('template-parts/search/pagination', null, [
 *     'previous_url' => '#',
 *     'next_url' => '#',
 *     'pages' => [
 *       [
 *         'title' => '1',
 *         'url' => '#',
 *         'current' => true,
 *       ],
 *       [
 *         'title' => '2',
 *         'url' => '#',
 *       ],
 *    ]
 *  ]);
 */

defined('ABSPATH') || exit;

$defaults = [
    'previous_url' => null,
    'next_url' => null,
    'pages' => [],
];

$args = array_merge($defaults, $args);

if (sizeof($args['pages']) > 1) {
?>

<nav class="pagination" aria-label="pagination">
    <ul class="pagination__list">
        <li class="pagination__link-wrapper pagination__link-wrapper--previous">
            <?php if ($args['previous_url']) : ?>
                <a class="pagination__link pagination__link--previous" href="<?= esc_url($args['previous_url']) ?>">
                    <span class="pagination__link-arrow" aria-hidden="true">«</span>
                    <span class="pagination__link-text">Previous</span>
                </a>
            <?php else : ?>
                <a class="pagination__link pagination__link--previous disabled" role="link" aria-disabled="true">
                    <span class="pagination__link-arrow" aria-hidden="true">«</span>
                    <span class="pagination__link-text">Previous</span></a>
            <?php endif; ?>
        </li>

        <li class="pagination__link-wrapper">
            <ul class="pagination__list pagination__sublist">
                <?php if (sizeof($args['pages']) < 1) : ?>
                    <li class="pagination__link-wrapper">
                        <a class="pagination__link disabled" role="link" aria-disabled="true"
                           aria-current="page">1</a>
                    </li>

                <?php else : ?>
                    <?php foreach ($args['pages'] as $page) : ?>
                        <li class="pagination__link-wrapper">

                            <?php if ($page['current'] ?? false) : ?>
                                <a class="pagination__link disabled" role="link" aria-disabled="true"
                                   aria-current="page">
                                    <?= esc_html($page['title']) ?>
                                </a>
                            <?php elseif (empty($page['url'])) : ?>
                                <a class="pagination__link disabled" role="link" aria-disabled="true">
                                    <?= esc_html($page['title']) ?>
                                </a>
                            <?php else : ?>
                                <a class="pagination__link" href="<?= esc_url($page['url']) ?>"><?= esc_html($page['title']) ?></a>
                            <?php endif; ?>

                        </li>
                    <?php endforeach; ?>

                <?php endif; ?>
            </ul>
        </li>

        <li class="pagination__link-wrapper pagination__link-wrapper--next">
            <?php if ($args['next_url']) : ?>
                <a class="pagination__link pagination__link--next" href="<?= esc_url($args['next_url']) ?>">
                    <span class="pagination__link-text">Next</span>
                    <span class="pagination__link-arrow" aria-hidden="true">»</span>
                </a>
            <?php else : ?>
                <a class="pagination__link pagination__link--next disabled" role="link" aria-disabled="true">
                    <span class="pagination__link-text">Next</span>
                    <span class="pagination__link-arrow" aria-hidden="true">»</span>
                </a>
            <?php endif; ?>
        </li>
    </ul>
</nav>
<?php
}
