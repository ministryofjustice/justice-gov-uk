<?php
/**
 * Template name: Error Testing
 * Template Post Type: page
 *
 * Use this file to test error reporting.
 * It is within WordPress because WordPress modifies error options at runtime.
 */

defined('ABSPATH') || exit;

if (getenv('WP_ENV') !== 'development') {
    return;
}

error_log('Source function: `error_log`. Source file: page_errors.php');

trigger_error('Source function: `trigger_error`. Source file: page_errors.php', E_USER_WARNING);

new WP_Error('exception', 'Source function: `new WP_Error`. Source file: page_errors.php');

throw new Exception("Source function: `throw new Exception`. Source file: page_errors.php", 900);
