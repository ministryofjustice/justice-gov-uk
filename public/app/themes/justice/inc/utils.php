<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Utility functions, that may be used throughout the theme.
 */

class Utils
{

    /**
     * Truncate a string to a certain length.
     *
     * @param string $string The string to truncate.
     * @param int $length The length to truncate the string to.
     * @param string $append The string to append to the end of the truncated string.
     * @return string The truncated string.
     */

    public function truncateString(string $string, int $length = 100, string $append = '&hellip;'): string
    {
        $string = trim($string);

        if (strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0] . $append;
        }

        return $string;
    }

    /**
     * Find an entry in an array that matches a filter function.
     *
     * @param array $array The array to search.
     * @param callable $filter_function The filter function to use.
     * @return mixed|null The first entry in the array that matches the filter function.
     */

    public function arrayFind(array $array, callable $filter_function): mixed
    {
        foreach ($array as $entry) {
            if (call_user_func($filter_function, $entry) === true) {
                return $entry;
            }
        }
        return null;
    }
}
