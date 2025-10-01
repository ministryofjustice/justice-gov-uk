<?php


defined('ABSPATH') || exit;

use MOJ\Justice\PostMeta;

$post_meta = new PostMeta(\get_the_ID(), $args);

if ($post_meta->hasPanel('menu')) {
    get_template_part('template-parts/panels/menu');
}

if ($post_meta->hasPanel('search-filters')) {
    get_template_part('template-parts/panels/search-filters.v1');
}
