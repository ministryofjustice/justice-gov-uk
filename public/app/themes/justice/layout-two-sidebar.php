<?php

defined('ABSPATH') || exit;

use MOJ\Justice\Breadcrumbs;
use MOJ\Justice\PostMeta;
use MOJ\Justice\Utils;

$post_meta = new PostMeta();

?>

<div class="two-sidebars">
    <div class="two-sidebars__grid">
        <div class="two-sidebars__sidebar two-sidebars__sidebar--left">
            <?php Utils::getSidebarMulti('left') ?>
        </div>
        <article id="main-page-content" class="two-sidebars__article">

            <div class="two-sidebars__article-header">
                <?php get_template_part('template-parts/common/hero', null, [
                    'title' => get_the_title(),
                    'breadcrumbs' => Breadcrumbs::getTheBreadcrumbs(),
                ]); ?>

                <div class="two-sidebars__sidebar two-sidebars__sidebar--mobile">
                    <?php Utils::getSidebarMulti('right', ['mobile' => true]) ?>
                </div>
            </div>

            <div class="two-sidebars__article-content">
                <?php

                while (have_posts()) {
                    the_post();

                    get_template_part('template-parts/common/rich-text', null, [
                        'content' => apply_filters('the_content', get_the_content())
                    ]);
                }

                if ($post_meta->getMeta('_show_updated_at')) :
                    get_template_part('template-parts/common/updated-date', null, [
                        'date' => $post_meta->getModifiedAt(),
                    ]);
                endif;
                ?>
            </div>

        </article>
        <div class="two-sidebars__sidebar two-sidebars__sidebar--right">
            <?php Utils::getSidebarMulti('right') ?>
        </div>
    </div>
</div>