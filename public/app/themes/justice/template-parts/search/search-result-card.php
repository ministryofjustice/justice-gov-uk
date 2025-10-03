<?php

/*
 * A card to show a single search result
 *
 * Available variables:
 * - title: string The page title
 * - url: string The link to the page
 * - description: string A short description of the page (html allowed)
 * - date: string The publication date
 * - is_document: boolean Whether the result is a document (for download link)
 *
 * Example usage:
 * get_template_part('template-parts/search/search-result-card', null, [
 *     'title' => 'Civil - Civil Procedure Rules',
 *     'url' => 'https://www.justice.gov.uk/courts/procedure-rules/civil',
 *     'date' => '26 February 2019',
 *     'description' => '…testing a new bill of costs, Precedent AA, to reflect the costs',
 *     'is_document' => true,
 * ]);
 */

defined('ABSPATH') || exit;

$defaults = [
    'is_document' => false,
];

$args = array_merge($defaults, $args);

?>

<article class="search-result-card">
    <hgroup class="search-result-card__title-with-date">
        <h1 class="search-result-card__title">
            <?php if ($args['is_document']) :
                get_template_part('template-parts/common/file-download', null, [
                    'label' => $args['title'],
                    'language' => $args['language'] ?? null,
                    'format' => $args['format'] ?? null,
                    'url' => $args['url'],
                    'filesize' => $args['filesize'] ?? null,
                ]);
            else :
                get_template_part('template-parts/common/link', null, [
                    'label' => $args['title'],
                    'url' => $args['url'],
                ]);
            endif ?>
            <span class="search-result-card__date caption"><?= esc_html($args['date']) ?></span>
        </h1>
    </hgroup>
    <p class="search-result-card__description">
        <?= wp_kses($args['description'], ['p' => [], 'span' => ['class'], 'strong' => []]) ?>
    </p>
    <p class="search-result-card__url">
        <?= esc_html($args['url']) ?>
    </p>
</article>
