<?php

if (class_exists('PPVersionNotices\Module\TopNotice\Module')) {
    // Remove the 'revisionary' Upgrade to Pro notice
    add_filter(\PPVersionNotices\Module\TopNotice\Module::SETTINGS_FILTER, function ($settings) {
        if (isset($settings['revisionary']['message']) && str_contains($settings['revisionary']['message'], 'Upgrade to Pro')) {
            unset($settings['revisionary']);
        }

        return $settings;
    }, 99);
}

// Only allow pages for PublishPress Revisions (formerly Revisionary) plugin
add_filter('revisionary_enabled_post_types', fn () => ['page' => 1]);
add_filter('revisionary_archive_post_types', fn () => ['page' => 1]);
