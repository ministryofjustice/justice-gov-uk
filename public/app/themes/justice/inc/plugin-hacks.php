<?php

add_action('wp_enqueue_scripts', function () {
    // remove frontend
    wp_dequeue_script('ccfw-script-frontend');

    // unregister the manage script
    wp_deregister_script('ccfw-script');
    // load our own...
    wp_register_script(
        'ccfw-script',
        get_template_directory_uri() . '/dist/patch/js/ccfw-cookie-manage.js',
        ['jquery'],
        1.2,
        true
    );
}, 99); // call late because we can't dequeue the frontend otherwise
