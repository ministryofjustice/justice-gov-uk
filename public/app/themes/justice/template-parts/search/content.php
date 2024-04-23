<?php

defined('ABSPATH') || exit;

?>

<article>
    <h1>
        <a href="<?php the_permalink(); ?>" title="<?php the_permalink(); ?>">
            <?php the_title() ?>
        </a>
    </h1>

    <!-- QUERY BIASED SUMMARY (WITH DATE)-->
    <span class="date"><?= $args['modified_at'] ?></span>
    <span class="content">
        <?php the_excerpt(); ?>
    </span>

    <span style="color:#008000;"><?= $args['formatted_url'] ?></span>

</article>
