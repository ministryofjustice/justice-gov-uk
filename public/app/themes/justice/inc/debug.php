<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class Debug
{

    public function addHooks()
    {
        if (!defined('WP_ENV') || WP_ENV !== 'development') {
            return;
        }
        add_action('get_footer', [$this, 'theCodePanel']);
        add_action('get_footer', [$this, 'theInfoPanel']);
    }

    /**
     * theCodePanel
     * This function outputs a panel to the bottom let of the screen.
     * It's useful during development, to temporarily get values onto the screen.
     *
     * Example of use:
     *
     * add_filter('moj_debug_content', function ($content) {
     *   $content[] = []'my value'];
     *   return $content;
     * });
     *
     * or
     *
     * $debug->push(['my value']);
     */

    public function theCodePanel(): void
    {
        $contents = apply_filters('moj_debug_content', []);

        if (empty($contents)) {
            return;
        }

        get_template_part('template-parts/development/code', null, $contents);
    }

    /**
     * push
     * A helper function to get content into the debugPanel.
     */

    public function push($new_content)
    {
        add_filter('moj_debug_content', function ($content) use ($new_content) {
            $content[] = $new_content;
            return $content;
        });
    }

    /**
     * theInfoPanel
     * Show a static info panel, during development only.
     */

    public function theInfoPanel()
    {
        if (!defined('WP_ENV') || WP_ENV !== 'development') {
            return;
        }

        $source_url = null;

        if (is_search()) {
            $source_url = 'https://www.justice.gov.uk/search';
        }

        if (is_page()) {
            $source_url = get_post_meta(\get_the_ID(), '_source_url', true);
        }

        if (!$source_url) {
            return;
        }

        get_template_part('template-parts/development/info', null, ['source_url' => $source_url]);
    }
}
