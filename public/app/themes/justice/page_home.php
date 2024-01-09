<?php
/**
 *
 * Template name: Home
 * Template Post Type: page
 */
$post_id = get_the_ID();

get_header();
?>

    <div id="highlight-wrapper">
        <div class="container-wrapper">
            <div id="highlight">
                <div class="tab-group">
                    <!-- PAGE CONTENT -->
                    <!-- ------------------------------------ -->
                    <article>
                        <a><img src="/app/uploads/2023/11/scales-of-justice.jpeg" alt="Scales of justice" width="474" height="285" /></a>
                        <header>
                            <h1 class="title"><?php the_title(); ?></h1>
                            <span class="intro">
                                <?php the_content() ?>
                            </span>
                        </header>
                    </article>
                    <!-- ------------------------------------ -->
                    <!-- end/ PAGE CONTENT -->
                </div>
            </div>
        </div>
    </div>
    <div id="footer-bar">
        <div>
        </div>
    </div>
<?php

get_footer();
