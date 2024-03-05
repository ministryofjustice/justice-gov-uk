<?php
/**
 *
 * Template name: 3 column (Default)
 * Template Post Type: page
 */

use MOJ\Justice\PostMeta;

get_header();

$post_meta = new PostMeta();

?>

    <main role="main" id="content-wrapper">
        <div class="container-wrapper">

            <?php if ($post_meta->sideHasPanels('left')) { ?>
                <div id="content-left">
                    <?php get_sidebar(); ?>
                </div>
            <?php } ?>

            <div id="content">

                <?php get_template_part('template-parts/nav/breadcrumbs'); ?>

                <div class="device-only">
                    <div class="anchor-link anchor-top">
                        <div class="bar-left"></div>
                        <a href="#phonenav">Menu ≡</a>
                        <div class="bar-right"></div>
                    </div>
                </div>

                <div class="print-only">
                    <img src="<?php echo get_template_directory_uri() ?>/dist/img/logo-inv.png" alt="" title="">
                </div>

                <article>
                    <h1 class="title"><?php the_title(); ?></h1>
                    <div class="share-this"></div>

                    <!-- PAGE CONTENT -->
                    <!-- ------------------------------------ -->
                    <div class="article">
                        <?php the_content() ?>
                    </div>
                    <!-- ------------------------------------ -->
                    <!-- end/ PAGE CONTENT -->

                    <div class="share-this bottom">
                        <?php if ($post_meta->getMeta('_show_modified_at')) { ?>
                            <span class="right">Updated: <?php echo $post_meta->getModifiedAt(); ?></span>
                        <?php } ?>
                    </div>

                </article>
            </div>

            <?php if ($post_meta->sideHasPanels('right')) { ?>
                <div id="content-right">
                    <?php get_sidebar('right'); ?>
                </div>
            <?php } ?>
            
        </div>
    </main>

<?php

get_footer();
