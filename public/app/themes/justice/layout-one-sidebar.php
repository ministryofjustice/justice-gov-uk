<?php

defined('ABSPATH') || exit;

use MOJ\Justice\Breadcrumbs;
use MOJ\Justice\PostMeta;
use MOJ\Justice\Utils;

$post_meta = new PostMeta();

?>

<div class="one-sidebar one-sidebar--<?=  $post_meta->sideHasPanels('left') ? 'left' : 'right' ?>">
    <div class="one-sidebar__grid">

        <?php if ($post_meta->sideHasPanels('left')) : ?>
            <div class="one-sidebar__sidebar">
                <?php Utils::getSidebarMulti('left'); ?>
            </div>
        <?php endif; ?>

        <article id="main-page-content" class="one-sidebar__article">

            <div class="one-sidebar__article-header">
                <?php get_template_part('template-parts/common/hero', null, [
                    'title' => get_the_title(),
                    'breadcrumbs' => Breadcrumbs::getTheBreadcrumbs(),
                ]); ?>

                <div class="one-sidebar__sidebar one-sidebar__sidebar--mobile">
                    <?php Utils::getSidebarMulti('right', ['is_mobile' => true]); ?>
                </div>
            </div>

            <div class="one-sidebar__article-content">

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

        <?php if ($post_meta->sideHasPanels('right')) : ?>
            <div class="one-sidebar__sidebar one-sidebar__sidebar--right">
                <?php Utils::getSidebarMulti('right'); ?>
            </div>
        <?php endif; ?>

    </div>
</div>
