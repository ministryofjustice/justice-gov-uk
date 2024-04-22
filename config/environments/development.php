<?php
/**
 * Configuration overrides for WP_ENV === 'development'
 */

use Roots\WPConfig\Config;
use function MOJ\Justice\env;

Config::define('SAVEQUERIES', true);
Config::define('WP_DEBUG', true);
Config::define('WP_DEBUG_DISPLAY', true);
Config::define('WP_DEBUG_LOG', '/dev/stderr');
Config::define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
Config::define('SCRIPT_DEBUG', true);
Config::define('DISALLOW_INDEXING', true);

if (env('WP_OFFLOAD_MEDIA_PRESET')) {
    Config::define('WP_OFFLOAD_MEDIA_PRESET', env('WP_OFFLOAD_MEDIA_PRESET'));
}

Config::define('SENTRY_TRACES_SAMPLE_RATE', 1.0);
Config::define('SENTRY_PROFILE_SAMPLE_RATE', 1.0);
