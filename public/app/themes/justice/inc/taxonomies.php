<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A class related to taxonomies.
 *
 * Includes adding taxonomies to the theme and getting taxonomies for the search filter.
 */

class Taxonomies
{

    public const TAXONOMY_DEFAULT = [
        'object_type' => ['document', 'page'],
        'args' => [
            'show_admin_column' => true,
            'rewrite' => false,
            'show_in_rest' => true,
        ],
    ];

    public const TAXONOMY_SECTION = [
        'taxonomy' => 'section',
        'args' => [
            'labels' => [
                'name' => 'Sections',
                'singular_name' => 'Section',
                'plural_name' => 'Sections',
                'add_new_item' => 'Add New Section',
            ],
        ],
    ];

    public const TAXONOMY_ORGANISATION = [
        'taxonomy' => 'organisation',
        'args' => [
            'labels' => [
                'name' => 'Organisations',
                'singular_name' => 'Organisation',
                'plural_name' => 'Organisations',
                'add_new_item' => 'Add New Organisation',
            ],
        ],
    ];

    public const TAXONOMY_AUDIENCE = [
        'taxonomy' => 'audience',
        'args' => [
            'labels' => [
                'name' => 'Audiences',
                'singular_name' => 'Audience',
                'plural_name' => 'Audiences',
                'add_new_item' => 'Add New Audience',
            ],
        ],
    ];

    public const TAXONOMY_TYPE = [
        'taxonomy' => 'type',
        'args' => [
            'labels' => [
                'name' => 'Types',
                'singular_name' => 'Type',
                'plural_name' => 'Types',
                'add_new_item' => 'Add New Type',
            ],
            // Add rest base for custom endpoint, because `type` conflicts with wp core.
            'rest_base' => 'moj-type',
        ],
    ];

    public const TAXONOMIES = [
        self::TAXONOMY_SECTION,
        self::TAXONOMY_ORGANISATION,
        self::TAXONOMY_AUDIENCE,
        self::TAXONOMY_TYPE,
    ];

    public function addHooks(): void
    {
        add_action('init', [$this, 'registerTaxonomies']);
    }

    /**
     * Register taxonomies.
     *
     * @return void
     */

    public function registerTaxonomies(): void
    {
        // Enable tags for pages.
        register_taxonomy_for_object_type('post_tag', 'page');

        // Loop through taxonomies and register them.
        // Merge with default object type and args.
        foreach (self::TAXONOMIES as $taxonomy) {
            register_taxonomy(
                $taxonomy['taxonomy'],
                array_merge(self::TAXONOMY_DEFAULT['object_type'], isset($taxonomy['object_type']) ? $taxonomy['object_type'] : []),
                array_merge(self::TAXONOMY_DEFAULT['args'], isset($taxonomy['args']) ? $taxonomy['args'] : []),
            );
        }
    }

    /**
     * Returns an object with the taxonomies, labels and terms.
     *
     * @return array
     */

    public function getTaxonomiesForFilter() : array
    {
        // Get all taxonomies.
        $taxonomies = get_object_taxonomies('page', 'objects');

        // Filter out post_tag.
        $taxonomies = array_filter($taxonomies, fn ($taxonomy) =>  $taxonomy->name !== 'post_tag');

        // Map over the taxonomies and return an object with the name, label and terms.
        return array_map(fn ($taxonomy) => (object) [
            'name' => $taxonomy->name,
            'label' => $taxonomy->labels->singular_name,
            // Map over the terms here and return an object with the name, slug and selected.
            'terms' => array_map(
                fn ($term) => (object) [
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'selected' => isset($_GET[$taxonomy->name]) && $_GET[$taxonomy->name] === $term->slug,
                ],
                get_terms([
                    'taxonomy'   =>  $taxonomy->name,
                    'hide_empty' => false,
                ])
            ),
        ], $taxonomies);
    }
}
