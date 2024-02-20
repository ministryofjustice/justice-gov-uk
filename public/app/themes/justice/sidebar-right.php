<?php

use MOJ\Justice\Meta;

$meta = new Meta();

global $post;

if ($meta->hasPanel('brand', $post->ID)) {
    get_template_part('template-parts/panels/brand');
}

if ($meta->hasPanel('archived', $post->ID)) {
    get_template_part('template-parts/panels/archived');
}

if ($meta->hasPanel('most_popular', $post->ID)) {
    get_template_part('template-parts/panels/most-popular');
}

if ($meta->hasPanel('related_content', $post->ID)) {
    get_template_part('template-parts/panels/related-content');
}

if ($meta->hasPanel('contact', $post->ID)) {
    get_template_part('template-parts/panels/contact');
}

if ($meta->hasPanel('email_alerts', $post->ID)) {
    get_template_part('template-parts/panels/email-alerts');
}

if ($meta->hasPanel('find_form', $post->ID)) {
    get_template_part('template-parts/panels/find-form');
}

if ($meta->hasPanel('find_court_form', $post->ID)) {
    get_template_part('template-parts/panels/find-court-form');
}

if ($meta->hasPanel('other_websites', $post->ID)) {
    get_template_part('template-parts/panels/other-websites');
}
