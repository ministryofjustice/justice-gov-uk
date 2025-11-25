<?php

defined('ABSPATH') || exit;

use MOJ\Justice\Search;
use MOJ\Justice\Taxonomies;

$taxonomies = (new Taxonomies())->getTaxonomiesForFilterV1();

if (empty($taxonomies)) {
    return;
}

?>

<div class="filter">
    <form action="<?= Search::getFormAction(); ?>">
        <span class="title">Filter results by:</span>

        <?php foreach ($taxonomies as $taxonomy) : ?>
            <div>

                <label for="filter-<?= $taxonomy->name; ?>"><?= $taxonomy->label; ?></label>

                <select id="filter-<?= $taxonomy->name; ?>" name="<?= $taxonomy->name; ?>">

                    <option value="">All</option>
                    <?php foreach ($taxonomy->terms as $term) : ?>
                        <option value="<?= $term->slug; ?>" <?= $term->selected ? "selected" : '' ?>>
                            <?= $term->name; ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

        <?php endforeach; ?>

        <label for="filter-web-only" id="filter-web-only-label">Web Pages Only</label>
        <?php
        /*
         * There is a known bug where post_type=page is not being passed to the search query.
         * Relevanssi introduces a new query var, post_types, to handle this.
         * @see https://www.relevanssi.com/knowledge-base/post_type-page-fail/
         */
        ?>
        <input type="checkbox" name="post_types" id="filter-web-only" value="page" <?= get_query_var('post_types') === 'page' ? 'checked="checked"' : '' ?> />

        <?php if (in_array(get_query_var('orderby'), ['date', 'relevance'])) : ?>
            <input type="hidden" name="orderby" value="<?= get_query_var('orderby') ?>">
        <?php endif; ?>

        <?php if (get_post((int) get_query_var('parent'))) : ?>
            <input type="hidden" name="parent" value="<?= (int) get_query_var('parent') ?>">
        <?php endif; ?>

        <div class="filter-btns">
            <input class="go-btn" type="submit" value="Apply filter" />
        </div>

    </form>
</div>
