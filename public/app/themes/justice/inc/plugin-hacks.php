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
    // wp_localize_script('ccfw-script-frontend', 'mojCcfwConfig', ['https' => wp_is_using_https()]);
    wp_localize_script('ccfw-script-frontend', 'mojCcfwConfig', ['https' => true]);
});
