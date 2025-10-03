<?php

/*
* A search bar block for a search page. Includes the number of results, filters and a 'did you mean?' suggestion
*
* Available variables:
* - search_form: array The variables for the text-input component
* - did_you_mean: array A suggested alternative search term
*   - term: string The alternative term
*   - url: string The url to search again with the new term
* - result_count: int The number of results
* - filters: array An array of sort filters
*   - selected: boolean Whether the filter is currently selected
*   - label: string The label for the filter
*   - url: string The link to apply the filter
*
* Example usage:
* get_template_part('template-parts/search/search-bar-block', null, [
*     'search_form' => [
*         'action' => '/',
*         'id' => 'search-main',
*         'input' => [
*             'labelHidden' => true,
*             'label' => 'Search',
*             'id' => 'search-main-input',
*         ],
*         'button' => [
*             'button_text' => 'Search'
*         ]
*     ],
*     'did_you_mean' => [
*         'term' => 'alternative term',
*         'url' => '/search?query=alternative+term'
*     ],
*     'result_count' => 42,
*     'filters' => [
*         [
*             'label' => 'Relevance',
*             'selected' => true,
*             'url' => '/search?sort=relevant'
*         ],
*         [
*             'label' => 'Date',
*             'selected' => false,
*             'url' => '/search?sort=date'
*         ]
*     ]
* ]);
*/

defined('ABSPATH') || exit;

if (!isset($args['search_form'])) {
    return;
}

$defaults = [
    'did_you_mean' => null,
    'result_count' => null,
    'filters' => [],
];

$args = array_merge($defaults, $args);

?>

<div class="search-bar-block">
    <div class="search-bar-block__wrapper">

        <div class="search-bar-block__search">
            <?php get_template_part('template-parts/common/text-input-form', null, $args['search_form']); ?>
        </div>

        <?php if ($args['result_count']) : ?>
            <div class="search-bar-block__results">
                <p class="search-bar-block__results-text">
                    <?php printf(_n('%d result', '%d results', $args['result_count']), $args['result_count'])  ?>
                </p>
            </div>
        <?php endif; ?>

    </div>

    <?php if (sizeof($args['filters']) && $args['result_count']) : ?>
        <div class="search-bar-block__filters-wrapper">
            <p class="search-bar-block__filters-text">Sort by:</p>
            <div class="search-bar-block__filters">
                <?php foreach ($args['filters'] as $filter) : ?>
                    <?php if ($filter['selected']) : ?>
                        <a class="search-bar-block__filter disabled" role="link" aria-current="page"
                            aria-disabled="true"><?= esc_html($filter['label']) ?></a>
                    <?php else : ?>
                        <a class="search-bar-block__filter" href="<?= esc_url($filter['url']) ?>"><?= esc_html($filter['label']) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($args['did_you_mean']['url']) && !empty($args['did_you_mean']['term'])) : ?>
        <div class="search-bar-block__did-you-mean">
            <p>Did you mean: <a href="<?= esc_url($args['did_you_mean']['url']) ?>"><?= esc_html($args['did_you_mean']['term']) ?></a>?</p>
        </div>
    <?php endif; ?>

</div>
