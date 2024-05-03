<?php

use MOJ\Justice\PostMeta;

$post_meta = new PostMeta(\get_the_ID(), $args);

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
    get_template_part('template-parts/panels/popular');
}

if ($post_meta->hasPanel('other_websites')) {
    get_template_part('template-parts/panels/other-websites');
}
