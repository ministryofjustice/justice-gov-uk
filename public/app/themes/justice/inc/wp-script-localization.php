<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * Class to convert localization scripts from <script type="text/javascript">
 * to <script type="application/json"> and load them via a custom loader function.
 *
 * Unfortunately, the way that WordPress adds data to the global window object, for
 * enqueued scripts, is not compatible with a strict Content Security Policy (CSP).
 *
 * WordPress stores data from wp_localize_script() as a `var name = {...};` string
 * on the script handle, and prints it as an inline script just before the handle's
 * <script src> tag (WP_Scripts::do_item -> print_extra_script).
 *
 * This class works with core hooks only:
 * - Just before scripts are printed, it reads each allowed handle's localized data
 *   string, converts it back to JSON, stores it, and blanks the handle's data so
 *   core prints no inline JS.
 * - On the `script_loader_tag` filter, it prepends a <script type="application/json">
 *   tag, plus a call to the loader function, in front of the handle's script tag.
 *
 * The localized data is then loaded via a custom JavaScript function
 * mojLoadLocalizedData() which is included in the script-localization.js file.
 *
 * @see justice/src/js/script-localization.js
 */
class WpScriptLocalization
{
    // The handle for the loader script registered in registerLocalizeLoaderScript().
    const LOADER_HANDLE = 'moj-localize-loader';

    // Only allow certain script handles to be modified.
    // Warning! Keep in sync with mojLocalizedDataEntries in src/js/script-localization.js.
    const ALLOWED_SCRIPT_HANDLES = [
        'cookie-consent-script',
        'wp-sentry-browser',
    ];

    // Let's make a generic inline script to call the loader function.
    // Warning! If this is modified, then the CSP in Nginx's config must also be updated.
    const LOAD_DATA_INLINE_SCRIPT = "<script type='text/javascript'>" .
        "(function() { if (typeof mojLoadLocalizedData === 'function') { mojLoadLocalizedData(); } })();" .
        "</script>\n";

    /**
     * Captured localized data, as JSON strings, keyed by script handle.
     */
    private array $captured = [];


    public function __construct()
    {
        $this->addHooks();
    }


    /**
     * Add the necessary hooks to capture localized data and re-emit it as JSON.
     */
    public function addHooks(): void
    {
        // Load the script-localization.js script.
        // This script contains the mojLoadLocalizedData() function
        // which is used to load variables from script tags with type="application/json".
        add_action('wp_enqueue_scripts', [$this, 'registerLocalizeLoaderScript']);

        // Add the loader as a dependency of the allowed scripts.
        add_action('wp_enqueue_scripts', [$this, 'addMojLocalizeLoaderAsDependency'], 100);

        // Capture and blank localized data just before head and footer scripts are printed.
        add_action('wp_print_scripts', [$this, 'captureAndBlankLocalizedData']);
        add_action('wp_print_footer_scripts', [$this, 'captureAndBlankLocalizedData'], 0);

        // Re-emit the captured data in front of each handle's <script src> tag.
        add_filter('script_loader_tag', [$this, 'prependJsonTag'], 10, 2);
    }


    /**
     * Load the localize loader script.
     *
     * This script is used to load localized data from script tags with type="application/json".
     * It is used to load data for scripts that are not registered in WordPress, such as Sentry.
     *
     * @return void
     */
    public function registerLocalizeLoaderScript(): void
    {
        $script_asset_path = get_template_directory() . '/dist/php/script-localization.min.asset.php';
        $script_uri = get_template_directory_uri() . '/dist/script-localization.min.js';

        $script_asset = file_exists($script_asset_path) ? require $script_asset_path : null;

        if (!is_array($script_asset) || !isset($script_asset['dependencies'], $script_asset['version'])) {
            wp_die(
                sprintf(
                    /* translators: %s: script-localization.min.asset.php */
                    __('The file %s is missing or invalid. Please run <code>npm run build</code> to create it.', 'justice'),
                    esc_html('script-localization.min.asset.php')
                ),
                __('Error', 'justice'),
                ['response' => 500]
            );
        }

        wp_register_script(
            self::LOADER_HANDLE,
            $script_uri,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );
    }


    /**
     * Add the moj-localize-loader script as a dependency to allowed scripts.
     *
     * This ensures that, when the allowed scripts are enqueued, the
     * moj-localize-loader script is also enqueued before them.
     *
     * @return void
     */
    public function addMojLocalizeLoaderAsDependency(): void
    {
        $scripts = wp_scripts();

        foreach (self::ALLOWED_SCRIPT_HANDLES as $handle) {
            if (isset($scripts->registered[$handle])) {
                $scripts->registered[$handle]->deps[] = self::LOADER_HANDLE;
            }
        }
    }


    /**
     * Capture the localized data of allowed handles and blank it,
     * so that core prints no inline JS for them.
     *
     * Runs on wp_print_scripts and wp_print_footer_scripts, i.e. after plugins
     * have called wp_localize_script() but before the data is printed.
     *
     * @return void
     */
    public function captureAndBlankLocalizedData(): void
    {
        // The loader is only registered on frontend requests (wp_enqueue_scripts).
        // On admin and login pages leave core's inline output alone - the CSP allows it there.
        if (!wp_script_is(self::LOADER_HANDLE, 'registered')) {
            return;
        }

        $scripts = wp_scripts();

        foreach (self::ALLOWED_SCRIPT_HANDLES as $handle) {
            $data = $scripts->get_data($handle, 'data');

            if (!$data) {
                continue;
            }

            $json = $this->localizedDataToJson($data);

            if ($json === null) {
                // Unexpected format - leave core's output untouched rather than lose the data.
                continue;
            }

            $this->captured[$handle] = $json;
            $scripts->add_data($handle, 'data', '');
        }
    }


    /**
     * Convert core's localized data string back to a JSON object string.
     *
     * WP_Scripts::localize() stores one single-line statement per localized object,
     * e.g. `var wp_sentry = {"dsn":"..."};` - json_encode never emits raw newlines,
     * so splitting on newlines is safe.
     *
     * @param string $data The localized data string, e.g. `var wp_sentry = {"dsn":"..."};`.
     *
     * @return string|null A JSON object string keyed by object name, or null on unexpected format.
     */
    private function localizedDataToJson(string $data): ?string
    {
        $pairs = [];

        foreach (explode("\n", $data) as $line) {
            if (!preg_match('/^var (\w+) = (.*);$/', $line, $matches)) {
                return null;
            }
            $pairs[] = json_encode($matches[1]) . ':' . $matches[2];
        }

        return $pairs ? '{' . implode(',', $pairs) . '}' : null;
    }


    /**
     * Prepend the captured data, as a JSON script tag, to the handle's script tag.
     *
     * Also prepends an inline script to call mojLoadLocalizedData(), which loads
     * the JSON data into global variables. The loader script itself is printed
     * earlier in the document, as it is a dependency of the handle.
     *
     * @param string $value  The script tag, e.g. `<script src="..."></script>`.
     * @param string $handle The handle of the enqueued script.
     *
     * @return string The script tag, with the JSON tag and loader call prepended.
     */
    public function prependJsonTag($value, $handle)
    {
        if (empty($this->captured[$handle])) {
            return $value;
        }

        $tag = sprintf(
            "<script type='application/json' id='%s-js-extra'>%s</script>\n",
            esc_attr($handle),
            $this->captured[$handle]
        );

        $tag .= self::LOAD_DATA_INLINE_SCRIPT;

        return $tag . $value;
    }
}
