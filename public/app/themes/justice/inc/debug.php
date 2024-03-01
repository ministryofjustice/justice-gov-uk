<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class Debug
{

    public function registerHooks()
    {
        add_action('get_footer', [$this, 'theDebugPanel']);
    }

    /**
     * theDebugPanel
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

    public function theDebugPanel(): void
    {

        if (getenv('WP_ENV') !== 'development') {
            return;
        }

        $contents = apply_filters('moj_debug_content', []);

        if (empty($contents)) {
            return;
        }

        echo "<pre class='moj-debug-panel'>";
        foreach ($contents as $content) {
            var_dump($content);
        }
        echo "</pre>";
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
}
