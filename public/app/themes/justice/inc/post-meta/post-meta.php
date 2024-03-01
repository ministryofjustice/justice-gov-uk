<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

require 'constants.php';

class PostMeta
{

    protected int | false $post_id = 0;

    /**
     * Constructor.
     */

    public function __construct(int | string $post_id = 0)
    {
        $this->post_id = $post_id ? (int) $post_id : \get_the_ID();
    }

    /**
     * Register hooks.
     * This isn't called within the constructor because it's only needs to be called once.
     */

    public function registerHooks()
    {
        $post_meta_constants = new PostMetaConstants();
        add_filter('sgf_register_fields', [$post_meta_constants, 'navigationFields'], 5);
        add_filter('sgf_register_fields', [$post_meta_constants, 'metaFields'], 5);
        add_filter('sgf_register_fields', [$post_meta_constants, 'panelFields'], 5);
    }

    /**
     * Check if a panel is enabled.
     */

    public function hasPanel(string $panel, string | int $post_id = 0): bool
    {
        return get_post_meta($post_id ?: $this->post_id, "_panel_$panel", true);
    }

    /**
     * Get short title.
     */

    public function getShortTitle(string | int $post_id = 0): string
    {
        $short_title = get_post_meta($post_id ?: $this->post_id, '_short_title', true);

        return $short_title && strlen($short_title) ? $short_title : get_the_title($post_id ?: $this->post_id);
    }

    /**
     * Get the modified at date.
     */

    public function getModifiedAt(string | int $post_id = 0): string
    {
        $date_format = 'l, j F Y';
        try {
            $modified_at_override = get_post_meta($post_id ?: $this->post_id, '_modified_at_override', true);
            return $modified_at_override ? date($date_format, strtotime($modified_at_override)) : get_the_modified_date($date_format);
        } catch (\Exception) {
            return get_the_modified_date($date_format);
        }
    }

    /**
     * Get the meta field.
     * A convenience wrapper around WordPress' get_post_meta.
     */

    public function getMeta(string $meta_key, string | int $post_id = 0, bool $single = true)
    {
        return get_post_meta($post_id ?: $this->post_id, $meta_key, $single);
    }
}
