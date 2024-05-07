<?php

defined('ABSPATH') || exit;

?>

<div class="search-bar">
    <form>

        <label for="searchbox">Search</label>
        <input name="s" id="query" type="text" value="<?= get_search_query() ?>" accesskey="q">
        <input class="go-btn" type="submit" value="<?= !empty($args['submit']) ? $args['submit'] : 'Go'; ?>">

        <?php if (!empty($args['parent'])) : ?>
            <input type="hidden" name="parent" value="<?= $args['parent'] ?>">
        <?php endif; ?>

    </form>

    <?php if (!empty($args['result_count'])) : ?>
        <div class="search-info">
            <?= $args['result_count'] ?> result<?= $args['result_count'] === 1 ? '' : 's' ?>
        </div>
    <?php endif; ?>

    <?php relevanssi_didyoumean(get_search_query(), '<div class="search-suggestion">Did you mean: ', '</div>', 5); ?>

</div>
