<?php

// A list of search result cards

// Available variables:
//     - cards: array An array of search results
//         - title: string The page title
//         - url: string The link to the page
//         - description: string A short description of the page
//         - date: string The publication date
// example:
//     {% include '@components/search-result-list/search-result-list.html.twig' with {
//         cards: [
//             {
//                 title: 'Civil - Civil Procedure Rules',
//                 url: 'https://www.justice.gov.uk/courts/procedure-rules/civil',
//                 date: '26 February 2019',
//                 description: '…testing a new bill of costs, Precedent AA, to reflect the costs"
//             }
//         ]
//     }%}

/*
 * A list of search result cards
 *
 * Available variables:
 * - cards: array An array of search results
 *     - title: string The page title
 *     - url: string The link to the page
 *     - description: string A short description of the page (html allowed)
 *     - date: string The publication date
 *     - is_document: boolean Whether the result is a document (for download link)
 *     - format: string The file format (if is_document is true)
 *     - filesize: string The file size (if is_document is true)
 *     - language: string The language of the document (optional)
 *
 * Example usage:
 *   get_template_part('template-parts/search/search-result-list', null, [
 *     'cards' => [
 *       [
 *         'title' => 'Civil - Civil Procedure Rules',
 *         'url' => 'https://www.justice.gov.uk/courts/procedure-rules/civil',
 *         'date' => '26 February 2019',
 *         'description' => '…testing a new bill of costs, Precedent AA, to reflect the costs',
 *         'is_document' => true,
 *         'format' => 'PDF',
 *         'filesize' => '1.2 MB',
 *       ],
 *       ...
 *     ]
 *   ]);
 */

defined('ABSPATH') || exit;

?>

<div class="search-result-list">
    <?php if (!empty($args['cards'])) : ?>
        <ol class="search-result-list__list">
            <?php foreach ($args['cards'] as $card) : ?>
                <li class="search-result-list__element">
                    <?php
                    get_template_part('template-parts/search/search-result-card', null, [
                        'title' => $card['title'],
                        'url' => $card['url'],
                        'date' => $card['date'],
                        'description' => $card['description'] ?? '',
                        'language' => $card['language'] ?? null,
                        'is_document' => $card['is_document'] ?? false,
                        'format' => ($card['is_document'] ?? false) ? $card['format'] : null,
                        'filesize' => ($card['is_document'] ?? false) ? ($card['filesize'] ?? null) : null,
                    ]);
                    ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php else : ?>
        <div class="search-result-list__no-results">
            <?php

            $content = "<h2>No results</h2>";

            if (!empty($args['query'])) {
                $content .= "<p>Your search for <span class='search-result-list__no-results-query'>" . esc_html($args['query']) . "</span> did not return any results.</p>";
                $content .= "<p>Please try again with different keywords or filters.</p>";
            } else {
                $content .= "<p>Please enter a search query to begin.</p>";
            }

            get_template_part('template-parts/common/rich-text', null, [
                'content' => $content,
            ]);

            ?>
        </div>
    <?php endif; ?>
</div>