<?php

/**
 * include command classes for use in WP_CLI in this file
 *
 * @example include "MyCommand.php"
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

class Commands
{

    public function __construct()
    {
        add_filter('pre_tax_input', [$this, 'parseTaxInputJson']);
        add_filter('pre_post_tax_input', [$this, 'parseTaxInputJson']);
    }

    /**
     * Parse tax_input json string to array.
     *
     * This is used during the `wp create post` command to parse the --tax_input json string to an associative array.
     *
     * @param mixed $value
     * @return mixed
     */

    public function parseTaxInputJson($value)
    {
        if (gettype($value) !== 'string') {
            return $value;
        }

        try {
            // Remove slashes & convert json to array
            return json_decode(stripslashes($value));
        } catch (\Exception $e) {
            // Don't throw an error, just log it.
            error_log('Failed to parse tax_input json string: ' . $value);
        }

        return $value;
    }
}
