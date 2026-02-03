<?php

defined('ABSPATH') || exit;

use MOJ\Justice\Utils;

get_header();

$error_html = '
        <h2>Try:</h2>
        <ul>
            <li>Checking that there are no typos in the page address.</li>
            <li>You can also use the <a href="/search">search</a> or <a href="/">browse from the homepage</a> to find the information you need.</li>
            <li>If you\'ve reached this page by clicking on a link or file, it is likely that the item has been moved or deleted. Contact the editor team to let them know they\'ve got a broken link, <a href="mailto:web.comments@justice.gov.uk">web.comments@justice.gov.uk</a> and see if they can help you find what you were looking for.</li>
            <li>Retry your search using alternative words in case the document or page has been moved or renamed.</li>
        </ul>
';
?>

<div class="one-sidebar one-sidebar--right">
    <div class="one-sidebar__grid">
        <article id="main-page-content" class="one-sidebar__article">

            <div class="one-sidebar__article-header">
                <?php get_template_part('template-parts/common/hero', null, [
                    'title' => 'Page not found',
                    'eyebrow_text' => 'Page not found - 404',
                ]); ?>

                <div class="one-sidebar__sidebar one-sidebar__sidebar--mobile">
                    <?php Utils::getSidebarMulti('right', ['is_mobile' => true]); ?>
                </div>
            </div>

            <div class="one-sidebar__article-content">

                <?php
                get_template_part('template-parts/common/rich-text', null, ['content' => $error_html]);
                ?>

            </div>
        </article>

        <div class="one-sidebar__sidebar">
            <?php Utils::getSidebarMulti('right'); ?>
        </div>

    </div>
</div>

<?php

get_footer();
