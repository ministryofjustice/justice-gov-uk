<?php

use function MOJ\Justice\env;

/**
 * Directory containing all the site's files
 */
$root_dir = dirname(__DIR__);

/**
 * Initialise Sentry options
 */
define('WP_SENTRY_PHP_DSN', env('SENTRY_DSN'));
define('WP_SENTRY_BROWSER_DSN', env('SENTRY_DSN'));
define('WP_SENTRY_ENV', WP_ENV . (env('SENTRY_DEV_ID') ?? ''));

const WP_SENTRY_SEND_DEFAULT_PII = true;
const WP_SENTRY_ERROR_TYPES = E_ALL & ~E_NOTICE & ~E_USER_NOTICE;
const WP_SENTRY_BROWSER_LOGIN_ENABLED = false;
const WP_SENTRY_BROWSER_TRACES_SAMPLE_RATE = 0.3;
const WP_SENTRY_BROWSER_REPLAYS_SESSION_SAMPLE_RATE = 0.1; // replaysSessionSampleRate
const WP_SENTRY_BROWSER_REPLAYS_ON_ERROR_SAMPLE_RATE = 1.0; // replaysOnErrorSampleRate

require_once $root_dir . '/public/app/plugins/wp-sentry/wp-sentry.php';
