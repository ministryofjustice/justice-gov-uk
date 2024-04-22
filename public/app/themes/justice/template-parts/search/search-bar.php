<?php

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="search-bar">
    <form>
        
        <label for="searchbox">Search</label>
        <input name="s" id="query" type="text" value="<?= get_search_query() ?>" accesskey="q">
        <input class="go-btn" type="submit" value="Go">

    </form>

    <?php if($args['result_count'] !== null) : ?>
        <div class="search-info">
            <?= $args['result_count'] ?> result<?= $args['result_count'] === 1 ? '' : 's' ?>
        </div>
    <?php endif; ?>

    <div class="search-suggestion">
    </div>

</div>