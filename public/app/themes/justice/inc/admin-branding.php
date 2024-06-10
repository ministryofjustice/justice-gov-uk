<?php

/**
 * Manage ancillary scripts and styles for rebranding the console
 */
add_action('login_enqueue_scripts', function () {
    $version = 1.0;
    wp_enqueue_style(
        'justice-branding-login-css',
        get_template_directory_uri() . '/dist/css/login.min.css',
        '',
        $version
    );

    wp_enqueue_script(
        'justice-branding-login-js',
        get_template_directory_uri() . '/dist/js/login.min.js',
        null,
        $version,
        true
    );
}, 10);
