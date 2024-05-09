<?php

defined('ABSPATH') || exit;

?>

<article>
    <h1>
        <a href="<?php the_permalink(); ?>" title="<?php the_permalink(); ?>">
            <?php the_title() ?>
        </a>
    </h1>

    <span class="date"><?php the_date('j F Y'); ?></span>
    <span class="content">
        <?php the_excerpt(); ?>
    </span>

    <span style="color:#008000;"><?= $args['formatted_url'] ?></span>

</article>
