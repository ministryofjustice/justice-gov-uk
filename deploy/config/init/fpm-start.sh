#!/bin/sh

if wp core is-installed 2>/dev/null; then
    # WP is installed.

    # Register the current container/pod with the cluster.
    wp cluster-helper register-self
else
    # Fallback if WP is not installed.
    # This will happen during a first run on localhost.
    echo 'WordPress is not installed yet, so skipping command `wp cluster-helper register-self` in `fpm-start.sh`.'
fi
