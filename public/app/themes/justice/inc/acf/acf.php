<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * Hide the field `_dynamic_menu_exclude_this` on the edit screen,
 * for pages with specific tags.
 *
 * This functionality can be demonstrated by tagging a page with
 * the tag `frontmatter-collection`. The field will be hidden.
 *
 * @param array $field The ACF field array.
 * @return array|false The unmodified field array or false to hide the field.
 */
add_filter('acf/prepare_field/name=_dynamic_menu_exclude_this', function ($field) {

    // If MOJ\Justice\DynamicMenu class does not exist, return the field unmodified.
    if (!class_exists(DynamicMenu::class)) {
        return $field;
    }

    // Get an array of the page's tags.
    $page_tag_ids = wp_get_post_tags(get_the_ID(), ['fields' => 'ids']);

    // Get the excluded tags.
    $excluded_tag_ids = (new DynamicMenu())->getExcludedChildPagesTags();

    // Check for intersection.
    $intersect_array = array_intersect($page_tag_ids, $excluded_tag_ids);

    return empty($intersect_array) ? $field : false;
});

// Disable the custom post type and taxonomies feature of ACF,
// since we are not using ACF for custom post types and taxonomies.
add_filter('acf/settings/enable_post_types', '__return_false');

// Disable the options pages UI feature of ACF,
// since we are not using ACF options pages.
add_filter('acf/settings/enable_options_pages_ui', '__return_false');

// Hide the Admin menu entirely on production.
// It's not necessary, since we are defining field groups programmatically,
// and it prevents accidental changes to fields in production.
add_filter('acf/settings/show_admin', fn() => defined('WP_ENV') && WP_ENV !== 'production');
