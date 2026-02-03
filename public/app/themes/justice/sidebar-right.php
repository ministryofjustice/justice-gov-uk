<?php

defined('ABSPATH') || exit;

use MOJ\Justice\ContentLinks;
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
    // Use ACF function if available, otherwise fall back to an empty array.
    $entries = function_exists('get_field') ? get_field('_panel_related_entries_acf') : [];

    get_template_part('template-parts/panels/list', null, [
        'title' => 'Related pages',
        'links' => array_map(
            function ($entry) {
                $args =  ContentLinks::getLinkParams(
                    $entry['url'],
                    $entry['label'] ?? null,
                    $entry['id'] ?? null,
                    $entry['target'] ?? null
                );

                return [...$args, ...$entry];
            },
            $entries ?? []
        ),
        'is_mobile' => $is_mobile,
    ]);
}

if ($post_meta->hasPanel('archived')) {
    get_template_part('template-parts/panels/list', null, [
        'title' => 'Archived pages',
        'links' => $post_meta::getArchiveLinks(),
        'is_archive' => true,
        'is_mobile' => $is_mobile,
    ]);
}

if ($post_meta->hasPanel('popular')) {
    get_template_part('template-parts/panels/list', null, [
        'title' => 'Most popular',
        'links' => $post_meta::getPopularLinks(),
        'is_mobile' => $is_mobile,
    ]);
}

if ($post_meta->hasPanel('other_websites')) {
    // Use ACF function if available, otherwise fall back to an empty array.
    $entries = function_exists('get_field') ? get_field('_panel_other_websites_entries_acf') : [];

    get_template_part('template-parts/panels/list', null, [
        'title' => 'Other websites',
        'links' => array_map(
            function ($entry) {
                $args =  ContentLinks::getLinkParams(
                    $entry['url'],
                    $entry['label'] ?? null,
                    $entry['id'] ?? null,
                    $entry['target'] ?? null
                );

                return [...$args, ...$entry];
            },
            $entries ?? []
        ),
        'is_mobile' => $is_mobile,
    ]);
}
