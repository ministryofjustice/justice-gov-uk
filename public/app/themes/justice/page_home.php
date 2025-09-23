<?php

use Roots\WPConfig\Config;
use MOJ\Justice\Utils;

if (Config::get('FRONTEND_VERSION') === 1) {
    require get_template_directory() . '/page_home.v1.php';
    return;
}

// Add skip link here, or to header?

/**
 *
 * Template name: Home
 * Template Post Type: page
 */

get_header();

get_template_part('template-parts/common/horizontal-rule', null, [
    'decorative' => true,
]);

?>
<div class="one-sidebar one-sidebar--right">
    <div class="one-sidebar__grid">
        <article id="main-page-content" class="one-sidebar__article one-sidebar__article--homepage">

            <div class="one-sidebar__article-header">
                <div class="one-sidebar__sidebar one-sidebar__sidebar--mobile">
                    <?php Utils::getSidebarMulti('right', ['is_mobile' => true]); ?>
                </div>
            </div>

            <div class="one-sidebar__article-content">

                <?php
                get_template_part('template-parts/common/image-with-text', null, [
                    'image' => [
                        'url' => get_template_directory_uri() . '/dist/img/scales-of-justice.jpg',
                        'alt' => 'A golden statue of Lady Justice holding a sword in her right hand and scales in her left, mounted on a domed building against a blue sky with clouds.',
                    ],
                    'title' => [
                        'tag' => 'h1',
                        'text' => get_the_title(),
                    ],
                    'content' => get_the_content(),
                ]);
                ?>

            </div>
        </article>

        <div class="one-sidebar__sidebar">
            <?php Utils::getSidebarMulti('right'); ?>
        </div>

    </div>
</div>
<?php

get_template_part('template-parts/common/horizontal-rule', null, [
    'decorative' => true,
]);

get_footer();
