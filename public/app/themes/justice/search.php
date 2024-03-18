<?php

get_header();

function formattedUrl ($url) {
    $url = str_replace('http://', '', $url);
    $url = str_replace('https://', '', $url);
    $url = str_replace('/', '/&#8203;', $url);
    // $url = str_replace('-', '&#8288;-&#8288;', $url);
    return $url;
}

?>

<main role="main" id="content-wrapper">

    <article class="container-wrapper">

        <div id="content-left">
            <h1 class="title">Search</h1>
            <?php get_sidebar('left', ['panels_in' => ['search-filters']]); ?>
        </div>

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

            <div class="search">

                <?php get_template_part('template-parts/search/search-bar') ?>

                <?php get_template_part('template-parts/search/sort') ?>

                <?php get_template_part('template-parts/search/pagination') ?>

                <div class="results">

                <?php
                    if ( have_posts() ) {
                        while ( have_posts() ) {
                            the_post();
                            get_template_part( 'template-parts/search/content', get_post_type() );
                        }
                    } else {
                        get_template_part('template-parts/search/no-results');
                    }
                    ?>

                </div>

                <?php get_template_part('template-parts/search/pagination') ?>

                <div class="device-only">
                    <?php
                    /*
                     * TODO. This could be improved with media queries.
                     * We shouldn't be echoing the same html twice.
                     */
                    ?>
                    <?php get_template_part('template-parts/panels/search-filters') ?>
                </div>
            </div>

        </div>

        <div id="content-right">
            <?php get_sidebar('right', ['panels_in' => ['search-find-a-form']]); ?>
        </div>

    </article>
</main>

<?php

get_footer();
