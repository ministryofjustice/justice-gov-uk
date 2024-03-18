<?php

use MOJ\Justice\Utils;



?>

<article>
    <h1>
        <a href="<?php the_permalink(); ?>" title="<?php the_permalink(); ?>">
            <?php the_title() ?>
        </a>
    </h1>

    <!-- QUERY BIASED SUMMARY (WITH DATE)-->
    <span class="date"><?php  ?></span>
    <span class="content">
        <?php the_excerpt(); ?>
    </span>

    <span style="color:#008000;">www.justice.gov.uk/downloads/offenders/probation-instructions<br>/pi-43-2014-eye-tests.doc</span>

    <span style="color:#008000; overflow-wrap: break-word; hyphens: none; overflow: hidden;"><?= formattedUrl(get_permalink()); ?></span>

</article>