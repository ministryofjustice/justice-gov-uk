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
                        <a>
                            <img src="<?php echo get_template_directory_uri() ?>/dist/img/scales-of-justice.jpg" alt="Scales of justice" width="474" height="285" />
                        </a>
                        <header>
                            <h1 class="title"><?php the_title(); ?></h1>
                            <div class="intro">
                                <?php the_content() ?>
                            </span>
                        </header>
                    </article>
                    <!-- ------------------------------------ -->
                    <!-- end/ PAGE CONTENT -->
                </div>

                <?php // TODO: make dynamic if necessary. ?>
                <div id="popular-wrapper">
                    <div id="popular">
                        <h2>Most popular</h2>
                        <ul>
                            <li><a href="<?php echo home_url('/courts/procedure-rules'); ?>">Procedure rules</a></li>
                            <li><a href="https://www.gov.uk/government/collections/royal-courts-of-justice-and-rolls-building-daily-court-lists">Daily court lists</a></li>
                            <li><a href="https://www.gov.uk/government/collections/prisons-in-england-and-wales">Prison finder</a></li>
                            <li><a href="https://www.gov.uk/courts/crown-courtt">XHIBIT daily court status</a></li>
                            <li><a href="https://www.gov.uk/guidance/prison-service-instructions-psis">Prison Service Instructions (PSIs)</a></li>
                            <li><a href="https://www.gov.uk/guidance/probation-instructions-pis">Probation Instructions</a></li>
                        </ul>
                    </div>
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
