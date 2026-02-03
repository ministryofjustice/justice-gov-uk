<?php

/**
 * Load the Composer autoloader for mu-plugins.
 *
 * This runs before other mu-plugins (alphabetically) to ensure
 * vendor classes are available.
 */

// Do not allow access outside WP
defined('ABSPATH') || exit;

// Possible autoloader locations
$autoloaders = [
    dirname(ABSPATH) . '/vendor/autoload.php',           // Normal: /project/vendor/autoload.php
    dirname(ABSPATH, 2) . '/vendor/autoload.php',        // WP scanner: /workspace/vendor/autoload.php
];

foreach ($autoloaders as $autoloader) {
    if (file_exists($autoloader)) {
        require_once $autoloader;
        break;
    }
}
