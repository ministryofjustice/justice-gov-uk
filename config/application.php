<?php

require 'env.php';

/**
 * Your base production configuration goes in this file. Environment-specific
 * overrides go in their respective config/environments/{{WP_ENV}}.php file.
 *
 * A good default policy is to deviate from the production config as little as
 * possible. Try to define as much of your configuration in this file as you
 * can.
 */

use Roots\WPConfig\Config;
use function MOJ\Justice\env;

/**
 * Directory containing all the site's files
 */
$root_dir = dirname(__DIR__);

/**
 * Document Root
 */
$webroot_dir = $root_dir . '/public';

/**
 * Use Dotenv to set required environment variables and load .env file in root
 * .env.local will override .env if it exists
 */
if (file_exists($root_dir . '/.env')) {
    $env_files = file_exists($root_dir . '/.env.local')
        ? ['.env', '.env.local']
        : ['.env'];

    $dotenv = Dotenv\Dotenv::createUnsafeImmutable($root_dir, $env_files, false);

    $dotenv->load();

    $dotenv->required(['WP_HOME', 'WP_SITEURL']);
    $dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASSWORD']);
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');

Config::define('WP_DEFAULT_THEME', 'justice');

/**
 * Infer WP_ENVIRONMENT_TYPE based on WP_ENV
 */
if (!env('WP_ENVIRONMENT_TYPE') && in_array(WP_ENV, ['production', 'staging', 'development', 'local'])) {
    Config::define('WP_ENVIRONMENT_TYPE', WP_ENV);
}

/**
 * URLs
 */
Config::define('WP_HOME', env('WP_HOME'));
Config::define('WP_SITEURL', env('WP_SITEURL'));

/**
 * Custom Content Directory
 */
Config::define('CONTENT_DIR', '/app');
Config::define('WP_CONTENT_DIR', $webroot_dir . Config::get('CONTENT_DIR'));
Config::define('WP_CONTENT_URL', Config::get('WP_HOME') . Config::get('CONTENT_DIR'));

/**
 * DB settings
 */
if (env('DB_SSL')) {
    Config::define('MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_SSL);
}

Config::define('DB_NAME', env('DB_NAME'));
Config::define('DB_USER', env('DB_USER'));
Config::define('DB_PASSWORD', env('DB_PASSWORD'));
Config::define('DB_HOST', env('DB_HOST') ?: 'localhost');
Config::define('DB_CHARSET', 'utf8mb4');
Config::define('DB_COLLATE', '');

// If the request origin is from a test suite, use the test database.
$is_test_request = (isset($_SERVER['HTTP_X_TEST_REQUEST']) && $_SERVER['HTTP_X_TEST_REQUEST'])
    || (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] === 'wp-browser')
    || env('WPBROWSER_HOST_REQUEST');

// Set the table prefix based on the request origin.
$table_prefix =  $is_test_request ? 'test_' : (env('DB_PREFIX') ?: 'wp_');


/**
 * Authentication Unique Keys and Salts
 */
Config::define('AUTH_KEY', env('AUTH_KEY'));
Config::define('SECURE_AUTH_KEY', env('SECURE_AUTH_KEY'));
Config::define('LOGGED_IN_KEY', env('LOGGED_IN_KEY'));
Config::define('NONCE_KEY', env('NONCE_KEY'));
Config::define('AUTH_SALT', env('AUTH_SALT'));
Config::define('SECURE_AUTH_SALT', env('SECURE_AUTH_SALT'));
Config::define('LOGGED_IN_SALT', env('LOGGED_IN_SALT'));
Config::define('NONCE_SALT', env('NONCE_SALT'));

/**
 * Custom Settings
 */
Config::define('AUTOMATIC_UPDATER_DISABLED', true);

// Disable the plugin and theme file editor in the admin
Config::define('DISALLOW_FILE_EDIT', true);

// Disable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_MODS', true);

// Limit the number of post revisions
Config::define('WP_POST_REVISIONS', env('WP_POST_REVISIONS') ?? true);

// API key for notifications.service.gov.uk email service
Config::define('GOV_NOTIFY_API_KEY', env('GOV_NOTIFY_API_KEY') ?? null);

// Define initial preset value for the wp-offload-media plugin.
Config::define('WP_OFFLOAD_MEDIA_PRESET', false);

// Sentry settings
Config::define('SENTRY_TRACES_SAMPLE_RATE', 0.3);
Config::define('SENTRY_PROFILE_SAMPLE_RATE', 0.3);

/**
 * Debugging Settings
 */
Config::define('WP_DEBUG_DISPLAY', false);
Config::define('WP_DEBUG_LOG', false);
Config::define('SCRIPT_DEBUG', false);
ini_set('display_errors', '0');

/**
 * Allow WordPress to detect HTTPS when used behind a reverse proxy or a load balancer
 * See https://codex.wordpress.org/Function_Reference/is_ssl#Notes
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

/**
 * WP Offload Media settings
 */

// By not setting AS3CF_SETTINGS here, we can use the plugin GUI to configure the settings during debugging.
if (file_exists(__DIR__ . '/wp-offload-media.php')) {
    require_once __DIR__ . '/wp-offload-media.php';
}

/**
 * Environment-specific settings
 */
$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';

if (file_exists($env_config)) {
    require_once $env_config;
}


Config::apply();

/**
 * Initialise Sentry
 */
if (env('SENTRY_DSN')) {
    Sentry\init([
        'dsn' => env('SENTRY_DSN'),
        'environment' => WP_ENV . (env('SENTRY_DEV_ID') ?? ''),
        'traces_sample_rate' => Config::get('SENTRY_TRACES_SAMPLE_RATE'),
        'profiles_sample_rate' => Config::get('SENTRY_PROFILE_SAMPLE_RATE')
    ]);
}

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/wp/');
}
