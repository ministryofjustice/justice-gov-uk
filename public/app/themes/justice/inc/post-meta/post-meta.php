<?php

namespace MOJ\Justice;

use Exception;

defined('ABSPATH') || exit;

require 'constants.php';

class PostMeta
{

    protected int | false $post_id = 0;
    protected array | null $panels_in = null;

    /**
     * Constructor.
     */

    public function __construct(int | string $post_id = 0, array $options = [])
    {
        $this->post_id = $post_id ? (int) $post_id : get_the_ID();
        if (isset($options['panels_in'])) {
            $this->panels_in = $options['panels_in'];
        }
    }

    /**
     * Register hooks.
     * This isn't called within the constructor because it's only needs to be called once.
     */

    public function addHooks()
    {
        $post_meta_constants = new PostMetaConstants();
        add_filter('sgf_register_fields', [$post_meta_constants, 'navigationFields'], 5);
        add_filter('sgf_register_fields', [$post_meta_constants, 'metaFields'], 5);
        add_filter('sgf_register_fields', [$post_meta_constants, 'panelFields'], 5);
        add_filter('document_title_parts', [$this, 'titleTagFilter']);
    }

    /**
     * Check if a panel is enabled.
     */

    public function hasPanel(string $panel, string | int $post_id = 0): bool
    {
        if (isset($this->panels_in)) {
            return in_array($panel, $this->panels_in);
        }
        return get_post_meta($post_id ?: $this->post_id, "_panel_$panel", true);
    }

    /**
     * Check if a side has any panels.
     */

    public function sideHasPanels(string $side = null, string | int $post_id = 0): bool
    {
        switch ($side) {
            case 'left':
                return $this->hasPanel('menu', $post_id);
            case 'right':
                return $this->hasPanel('brand', $post_id)
                    || $this->hasPanel('search', $post_id)
                    || $this->hasPanel('email_alerts', $post_id)
                    || $this->hasPanel('related', $post_id)
                    || $this->hasPanel('archived', $post_id)
                    || $this->hasPanel('popular', $post_id)
                    || $this->hasPanel('other_websites', $post_id);
            default:
                // Call recursively.
                return $this->sideHasPanels('left', $post_id) || $this->sideHasPanels('right', $post_id);
        }
    }

    /**
     * Get short title.
     */

    public function getShortTitle(string | int | null $post_id = 0): string
    {
        $short_title = get_post_meta($post_id ?: $this->post_id, '_short_title', true);

        return $short_title && strlen($short_title) ? $short_title : get_the_title($post_id ?: $this->post_id);
    }

    /**
     * Get the modified at date.
     */

    public function getModifiedAt(string | int $post_id = 0, $date_format = 'l, j F Y'): string
    {
        try {
            $modified_at_override = get_post_meta($post_id ?: $this->post_id, '_modified_at_override', true);
            return $modified_at_override ? date($date_format, strtotime($modified_at_override)) : get_the_modified_date($date_format);
        } catch (Exception) {
            return get_the_modified_date($date_format);
        }
    }

    /**
     * Get the meta field.
     * A convenience wrapper around WordPress' get_post_meta.
     */

    public function getMeta(string $meta_key, string | int $post_id = 0, bool $single = true)
    {
        return get_metadata('post', $post_id ?: $this->post_id, $meta_key, $single);
    }

    /**
     * Filter the title.
     *
     * If metadata is set for the title tag, return that.
     * Otherwise, return the default title.
     *
     * @param array $title_parts
     * @return array
     */

    public function titleTagFilter(array $title_parts): array
    {
        $title_tag = $this->getMeta('_title_tag', get_the_ID());

        if (!empty($title_tag)) {
            $title_parts['title'] = trim($title_tag);
            unset($title_parts['tagline']);
            unset($title_parts['site']);
        }

        return $title_parts;
    }

    /**
     * Get the title for internal search.
     *
     * @return string
     */

    public function getSearchResultTitle(): string
    {
        $custom_title = $this->getMeta('_title_tag', get_the_ID());

        if (!empty(trim($custom_title))) {
            return $custom_title;
        }

        return get_the_title();
    }
}
