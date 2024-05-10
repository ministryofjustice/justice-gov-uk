<?php

defined('ABSPATH') || exit;

?>

<article>
    <h1>
        <a href="<?php the_permalink(); ?>" title="<?php the_permalink(); ?>">
            <?php the_title() ?>
        </a>
    </h1>

    <?php
    /*
     * Use get_the_date here because the_date will not echo any duplicate dates.
     */
    ?>
    <span class="date"><?= get_the_date('j F Y'); ?></span>
    <span class="content">
        <?php the_excerpt(); ?>
    </span>

    <span style="color:#008000;"><?= $args['formatted_url'] ?></span>

</article>