<?php

use MOJ\Justice\PostMeta;

$post_meta = new PostMeta();

if ($post_meta->hasPanel('brand')) {
    get_template_part('template-parts/panels/brand');
}

if ($post_meta->hasPanel('archived')) {
    get_template_part('template-parts/panels/archived');
}

if ($post_meta->hasPanel('most_popular')) {
    get_template_part('template-parts/panels/most-popular');
}

if ($post_meta->hasPanel('related_content')) {
    get_template_part('template-parts/panels/related-content');
}

if ($post_meta->hasPanel('contact')) {
    get_template_part('template-parts/panels/contact');
}

if ($post_meta->hasPanel('email_alerts')) {
    get_template_part('template-parts/panels/email-alerts');
}

if ($post_meta->hasPanel('find_form')) {
    get_template_part('template-parts/panels/find-form');
}

if ($post_meta->hasPanel('find_court_form')) {
    get_template_part('template-parts/panels/find-court-form');
}

if ($post_meta->hasPanel('other_websites')) {
    get_template_part('template-parts/panels/other-websites');
}
