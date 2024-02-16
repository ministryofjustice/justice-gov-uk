<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class Meta
{
    public function getShortTitle(string | int $post_id): string
    {
        $short_title = get_post_meta($post_id, 'short_title', true);

        return $short_title ? $short_title : get_the_title($post_id);
    }
}
