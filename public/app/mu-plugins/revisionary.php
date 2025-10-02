<?php

/**
 * Filter PublishPress Revisions (formerly Revisionary) options
 * to disable custom role capabilities.
 * This can't be in the theme directory because that's loaded too late.
 */

add_filter('options_rvy', function ($options) {
    $options['rvy_revisor_role_add_custom_rolecaps'] = 0;
    $options['approve_button_verbose'] = 1;
    return $options;
});
