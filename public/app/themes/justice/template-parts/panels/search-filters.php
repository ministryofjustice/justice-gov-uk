<?php

defined('ABSPATH') || exit;

use MOJ\Justice\Taxonomies;

$taxonomies = (new Taxonomies())->getTaxonomiesForFilter();

if (empty($taxonomies)) {
    return;
}

?>

<div class="filter">
    <form>
        <span class="title">Filter results by:</span>

        <?php foreach ($taxonomies as $taxonomy) : ?>
            <div>

                
                <label for="filter-<?= $taxonomy->name; ?>"><?= $taxonomy->label; ?></label>
                
                <select id="filter-<?= $taxonomy->name; ?>" name="<?= $taxonomy->name; ?>">
                    
                <option value="">All</option>
                <?php foreach ($taxonomy->terms as $term) : ?>
                    <option value="<?= $term->slug; ?>" <?= $term->selected ? "selected" : '' ?> >
                        <?= $term->name; ?>
                    </option>
                <?php endforeach; ?>
                    
                </select>
            </div>
                
        <?php endforeach; ?>

        <label for="filter-web-only" id="filter-web-only-label">Web Pages Only</label>
        <input type="checkbox" name="web-only" id="filter-web-only" value="!pdf" />

        <div class="filter-btns">
            <input class="go-btn" type="submit" value="Apply filter" />
        </div>

    </form>
</div>
