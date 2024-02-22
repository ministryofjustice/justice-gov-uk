<?php

use MOJ\Justice\PostMeta;

$post_meta = new PostMeta();

if ($post_meta->hasPanel('brand')) {
    get_template_part('template-parts/panels/brand');
}

if ($post_meta->hasPanel('search')) {
    get_template_part('template-parts/panels/search');
}

if ($post_meta->hasPanel('email_alerts')) {
    get_template_part('template-parts/panels/email-alerts');
}

if ($post_meta->hasPanel('archived')) {
    get_template_part('template-parts/panels/archived');
}

// TODO. Handling meta for this template is not implemented yet.
if ($post_meta->hasPanel('most_popular')) {
    get_template_part('template-parts/panels/most-popular');
}

// TODO. Handling meta for this template is not implemented yet.
if ($post_meta->hasPanel('related_content')) {
    get_template_part('template-parts/panels/related-content');
}

// TODO. Handling meta for this template is not implemented yet.
if ($post_meta->hasPanel('other_websites')) {
    get_template_part('template-parts/panels/other-websites');
}

if (defined('WP_ENV') && WP_ENV === 'development') {
    get_template_part('template-parts/panels/development');
}
