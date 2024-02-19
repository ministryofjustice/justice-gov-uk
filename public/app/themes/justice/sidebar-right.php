<?php

use MOJ\Justice\Panels;

$panels = new Panels();

if ($panels->hasBanner()) {
    get_template_part('template-parts/panels/banner');
}

if ($panels->hasMostPopular()) {
    get_template_part('template-parts/panels/most-popular');
}

if ($panels->hasRelatedContent()) {
    get_template_part('template-parts/panels/related-content');
}

if ($panels->hasContact()) {
    get_template_part('template-parts/panels/contact');
}

if ($panels->hasEmailAlerts()) {
    get_template_part('template-parts/panels/email-alerts');
}

if ($panels->hasFindForm()) {
    get_template_part('template-parts/panels/find-form');
}

if ($panels->hasFindCourtForm()) {
    get_template_part('template-parts/panels/find-court-form');
}
