<?php

namespace MOJ\Justice;

use Exception;

defined('ABSPATH') || exit;

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
        add_filter('document_title_parts', [$this, 'titleTagFilter']);
        add_action('wp_head', [$this, 'headMetaTags']);
    }

    /**
     * Check if a panel is enabled.
     */

    public function hasPanel(string $panel, string | int $post_id = 0): bool
    {
        if (isset($this->panels_in)) {
            return in_array($panel, $this->panels_in);
        }

        if (function_exists('get_field')) {
            // Use ACF to get the field if it's available.
            // This is important, since when migrated to ACF, get_metadata will not return the correct default value.
            return (bool) get_field("_panel_$panel", $post_id ?: $this->post_id);
        }

        // Fallback to get_post_meta if ACF is not available.
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
        if (function_exists('get_field')) {
            // Use ACF to get the field if it's available.
            // This is important, since when migrated to ACF, get_metadata will not return the correct default value.
            return get_field($meta_key, $post_id ?: $this->post_id);
        }

        // Fallback to get_metadata if ACF is not available.
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
            // Get the document title separator.
            $sep = apply_filters('document_title_separator', '-');
            // Replace hyphen and all types of dashes with the correct separator.
            $title_tag = str_replace(['-', '–', '—'], $sep, $title_tag);
            // Trim the title and assign it to the title parts.
            $title_parts['title'] = trim($title_tag);
            // Remove the tagline from the title parts.
            unset($title_parts['tagline']);
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

    /**
     * Get & echo the meta tags for the html head.
     *
     * This function uses data from WordPress meta, and taxonomy terms.
     *
     * @return void
     */

    public function headMetaTags(): void
    {
        $meta_data = (new Taxonomies())->getTaxonomiesForHeaderMeta();

        $meta_data['modified'] = $this->getModifiedAt(get_the_ID(), 'Y-m-d');

        get_template_part('template-parts/head/meta', null, $meta_data);
    }


    public static function getArchiveLinks(): array
    {
        return [
            [
                'url' => 'https://webarchive.nationalarchives.gov.uk/*/http://www.justice.gov.uk/index.htm',
                'label' => 'Ministry of Justice archived websites (2007 to 2012)'
            ],
            [
                'url' => 'https://webarchive.nationalarchives.gov.uk/ukgwa/timeline/https:/www.justice.gov.uk/courts/procedure-rules',
                'label' => 'Ministry of Justice archived websites (2012 to present day)'
            ]
        ];
    }

    public static function getPopularLinks(): array
    {
        return [
            [
                'url' => home_url('/courts/procedure-rules'),
                'label' => 'Procedure rules'
            ],
            [
                'url' => 'https://www.gov.uk/government/collections/royal-courts-of-justice-and-rolls-building-daily-court-lists',
                'label' => 'Daily court lists'
            ],
            [
                'url' => 'https://www.gov.uk/government/collections/prisons-in-england-and-wales',
                'label' => 'Prison finder'
            ],
            [
                'url' => 'https://www.gov.uk/courts/crown-court',
                'label' => 'XHIBIT daily court status'
            ],
            [
                'url' => 'https://www.gov.uk/guidance/prison-service-instructions-psis',
                'label' => 'Prison service instructions (PSIs)'
            ],
            [
                'url' => 'https://www.gov.uk/guidance/probation-instructions-pis',
                'label' => 'Probation Instructions'
            ]
        ];
    }
}
