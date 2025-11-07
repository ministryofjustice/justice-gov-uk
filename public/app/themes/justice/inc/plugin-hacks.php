<?php

add_action('wp_enqueue_scripts', function () {
    wp_deregister_script('ccfw-script-frontend');
    wp_deregister_script('ccfw-script');

    wp_enqueue_script(
        'ccfw-script-frontend',
        get_template_directory_uri() . '/dist/patch/js/ccfw-frontend.js',
        ['jquery'],
        1.2,
        true
    );

    wp_enqueue_script(
        'ccfw-script',
        get_template_directory_uri() . '/dist/patch/js/ccfw-cookie-manage.js',
        ['jquery', 'ccfw-script-frontend'],
        1.2,
        true
    );

    // Localize the script with https data
    wp_localize_script('ccfw-script', 'ccfwData', [
        'isHttps' => (int) is_ssl(),
    ]);
});


/**
 * Override the script_loader_tag for ccfw-script to remove module/nomodule attributes
 *
 * This is necessary because modules require a CORS header which is not provided by our CloudFront setup.
 *
 * @param string $tag The original script tag.
 * @param string $handle The script handle.
 * @param string $src The script source URL.
 * @return string Modified script tag without module/nomodule attributes.
 */
function override_ccfw_script_type($tag, $handle, $src) {
    // Only affect the ccfw-script handle
    if ('ccfw-script' === $handle) {
        // Return the original tag without module/nomodule attributes
        return '<script id="ccfw-script" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}

add_filter('script_loader_tag', 'override_ccfw_script_type', 20, 3);


if (class_exists('PPVersionNotices\Module\TopNotice\Module')) {
    // Remove the 'revisionary' Upgrade to Pro notice
    add_filter(\PPVersionNotices\Module\TopNotice\Module::SETTINGS_FILTER, function ($settings) {
        if (isset($settings['revisionary']['message']) && str_contains($settings['revisionary']['message'], 'Upgrade to Pro')) {
            unset($settings['revisionary']);
        }

        return $settings;
    }, 99);
}

// Only allow pages for PublishPress Revisions (formerly Revisionary) plugin
add_filter('revisionary_enabled_post_types', fn () => ['page' => 1]);
add_filter('revisionary_archive_post_types', fn () => ['page' => 1]);
