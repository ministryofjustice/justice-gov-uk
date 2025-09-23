<?php

use Roots\WPConfig\Config;

if (Config::get('FRONTEND_VERSION') === 1) {
    require get_template_directory() . '/sidebar-right.v1.php';
    return;
}

use MOJ\Justice\PostMeta;

$post_meta = new PostMeta(\get_the_ID(), $args);

$is_mobile = $args['is_mobile'] ?? false;

if ($post_meta->hasPanel('brand')) {
    get_template_part('template-parts/panels/brand');
}

if ($post_meta->hasPanel('search')) {
    get_template_part('template-parts/panels/search');
}

if ($post_meta->hasPanel('email_alerts')) {
    get_template_part('template-parts/panels/email-alerts');
}

if ($post_meta->hasPanel('related')) {
    get_template_part('template-parts/panels/related');
}

if ($post_meta->hasPanel('archived')) {
    get_template_part('template-parts/panels/archived');
}

if ($post_meta->hasPanel('popular')) {
    get_template_part('template-parts/panels/list', null, [
        'title' => 'Most popular',
        'links' => $post_meta::getPopularLinks(),
        'is_mobile' => $is_mobile,
    ]);
}

if ($post_meta->hasPanel('other_websites')) {
    get_template_part('template-parts/panels/other-websites');
}
