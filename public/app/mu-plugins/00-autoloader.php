<?php

/**
 * Load the Composer autoloader and application config for mu-plugins.
 *
 * This runs before other mu-plugins (alphabetically) to ensure
 * vendor classes and config are available.
 */

// Do not allow access outside WP
defined('ABSPATH') || exit;

// Possible base paths (where vendor/ and config/ directories are located)
$basePaths = [
    dirname(ABSPATH),       // Normal: /project/public/wp -> /project
    dirname(ABSPATH, 2),    // WP scanner: /workspace/wordpress -> /workspace
];

foreach ($basePaths as $basePath) {
    $autoloader = $basePath . '/vendor/autoload.php';
    $appConfig = $basePath . '/config/application.php';

    if (file_exists($autoloader)) {
        require_once $autoloader;

        // Also load the application config if available
        if (file_exists($appConfig)) {
            require_once $appConfig;
        }
        break;
    }
}
