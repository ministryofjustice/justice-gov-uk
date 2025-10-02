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

    // This localised variable can be accessed by both `ccfw-script-frontend` and `ccfw-script`,
    // because it is declared before the scripts in the html.
    wp_localize_script('ccfw-script-frontend', 'mojCcfwConfig', ['https' => wp_is_using_https()]);
});

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
