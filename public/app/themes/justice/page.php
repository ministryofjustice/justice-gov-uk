<?php

use Roots\WPConfig\Config;

if (Config::get('FRONTEND_VERSION') === 1) {
    require get_template_directory() . '/page.v1.php';
    return;
}

use MOJ\Justice\PostMeta;

get_header();

$post_meta = new PostMeta();

if($post_meta->sideHasPanels('left') && $post_meta->sideHasPanels('right')) {
    require get_template_directory() . '/layout-two-sidebar.php';
} else {
    require get_template_directory() . '/layout-one-sidebar.php';
}

get_footer();
