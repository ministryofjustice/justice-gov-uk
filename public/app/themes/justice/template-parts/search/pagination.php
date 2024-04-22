<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!have_posts()) {
    return;
}

global $wp_query;

?>

<div class="nav">

    <span class="prev">
        <?php if ($wp_query->query_vars['paged'] > 1) : ?>
            <a href="<?= get_previous_posts_page_link(); ?>">« Previous</a>
        <?php else : ?>
            « Previous
        <?php endif; ?>
    </span>

    <ul>
        <?php for ($i = 1; $i <= min(5, $wp_query->max_num_pages); $i++) : ?>
            <li <?php echo $i === $wp_query->query_vars['paged'] ? 'class="selected"' : ''; ?>>
                <?php if ($i === $wp_query->query_vars['paged']) : ?>
                    <span><?php echo $i; ?></span>
                <?php else : ?>
                    <a href="<?php echo get_pagenum_link($i); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            </li>
        <?php endfor; ?>
    </ul>

    <span class="next">
        <?php if ($wp_query->max_num_pages > $wp_query->query_vars['paged']) : ?>
            <a href="<?= get_next_posts_page_link(); ?>">Next »</a>
        <?php else : ?>
            Next »
        <?php endif; ?>
    </span>

</div>
