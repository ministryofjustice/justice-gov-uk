<?php

add_action('wp_enqueue_scripts', function () {
    wp_deregister_script('ccfw-script-frontend');
    wp_deregister_script('ccfw-script');

    wp_enqueue_script(
        'ccfw-script-frontend',
        get_template_directory_uri() . '/dist/patch/js/ccfw-frontend.js',
        ['jquery'],
        1.0,
        true
    );

    wp_enqueue_script(
        'ccfw-script',
        get_template_directory_uri() . '/dist/patch/js/ccfw-cookie-manage.js',
        ['jquery'],
        1.0,
        true
    );
});
