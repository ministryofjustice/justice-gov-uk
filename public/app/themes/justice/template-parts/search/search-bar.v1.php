<?php

defined('ABSPATH') || exit;

use MOJ\Justice\Taxonomies;

$taxonomies = (new Taxonomies())->getTaxonomiesForHiddenInputs();

?>

<div class="search-bar">
    <form>

        <label for="query">Search</label>
        <input name="s" id="query" type="text" value="<?= get_search_query() ?>" accesskey="q">
        <input class="go-btn" type="submit" value="<?= !empty($args['submit']) ? $args['submit'] : 'Go'; ?>">

        <?php if (in_array(get_query_var('orderby'), ['date', 'relevance'])) : ?>
            <input type="hidden" name="orderby" value="<?= get_query_var('orderby') ?>">
        <?php endif; ?>

        <?php if (get_post((int) $args['parent'])) : ?>
            <input type="hidden" name="parent" value="<?= (int) $args['parent'] ?>">
        <?php endif; ?>

        <?php if (get_query_var('post_types') === 'page') : ?>
            <input type="hidden" name="post_types" value="<?= get_query_var('post_types') ?>">
        <?php endif; ?>

        <?php foreach ($taxonomies as $taxonomy) : ?>
            <input type="hidden" name="<?= $taxonomy->name ?>" value="<?= esc_html($taxonomy->value) ?>">
        <?php endforeach; ?>

    </form>

    <?php if (!empty($args['result_count'])) : ?>
        <div class="search-info">
            <?= $args['result_count'] ?> result<?= $args['result_count'] === 1 ? '' : 's' ?>
        </div>
    <?php endif; ?>

    <?php relevanssi_didyoumean(get_search_query(), '<div class="search-suggestion">Did you mean: ', '</div>', 5); ?>

</div>
