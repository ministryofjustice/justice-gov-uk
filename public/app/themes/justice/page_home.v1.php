<?php

defined('ABSPATH') || exit;

get_header();

?>

    <div id="highlight-wrapper">
        <div class="container-wrapper">
            <div id="highlight">
                <div class="tab-group">
                    <!-- PAGE CONTENT -->
                    <!-- ------------------------------------ -->
                    <article>
                        <a>
                            <img src="<?php echo get_template_directory_uri() ?>/dist/img/scales-of-justice.jpg" alt="Scales of justice" width="474" height="285" />
                        </a>
                        <header>
                            <?php the_content() ?>
                        </header>
                    </article>
                    <!-- ------------------------------------ -->
                    <!-- end/ PAGE CONTENT -->
                </div>

                <?php get_sidebar('right'); ?>

            </div>
        </div>
    </div>
    <div id="footer-bar">
        <div>
        </div>
    </div>
<?php

get_footer();
