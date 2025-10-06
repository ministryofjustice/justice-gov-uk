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
});

add_filter('moj_safe_localization_data', function ($data) {
    // Add the is-https data attribute to the html tag - for ccfw-storage.js.
    $data['is-https'] = (int) wp_is_using_https();
    return $data;
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

