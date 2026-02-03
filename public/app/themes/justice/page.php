<?php

/**
 * The template for displaying a generic page
 */

defined('ABSPATH') || exit;

use MOJ\Justice\PostMeta;

get_header();

$post_meta = new PostMeta();

if ($post_meta->sideHasPanels('left') && $post_meta->sideHasPanels('right')) {
    require get_template_directory() . '/layout-two-sidebar.php';
} else {
    require get_template_directory() . '/layout-one-sidebar.php';
}

get_footer();
