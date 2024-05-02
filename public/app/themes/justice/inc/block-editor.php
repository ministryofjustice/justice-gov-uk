<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * BlockEditor
 * A class to handle block editor functionality.
 */

class BlockEditor
{

    public function addHooks()
    {
        add_action('init', [$this, 'registerBlocks']);
    }

    public function registerBlocks()
    {
        register_block_type('moj/inline-menu', ['render_callback' => [$this, 'inlineMenu']]);
        register_block_type('moj/search', ['render_callback' => [$this, 'search']]);
    }

    /**
     * templatePartToVariable
     * A helper function to render a template part to a variable.
     * Useful for use in render_callback functions for blocks.
     */

    public function templatePartToVariable($slug, $name = null, $args = array()): string
    {
        ob_start();
        get_template_part($slug, $name, $args);
        return ob_get_clean();
    }

    /**
     * inlineMenu
     * A render_callback function for the inline-menu block.
     * Gets the child pages of the current page and renders them as an inline list.
     */

    public function inlineMenu(): string
    {
        $post_id = get_the_ID();
        $post_meta = new PostMeta();
        $children = get_pages('parent=' . $post_id . '&sort_column=menu_order');
        $entries = [];

        if ($children) {
            foreach ($children as $child) {
                $entries[] = [
                    'title' => $post_meta->getShortTitle($child->ID),
                    'url' => \get_permalink($child->ID)
                ];
            }
        }

        return $this->templatePartToVariable('template-parts/common/inline-list', null, [
            'entries' => $entries
        ]);
    }

    /**
     * Render callback for the search block.
     *
     * Parent page is passed as an argument to the search form.
     * So that the search form will return results containing only children of the current page.
     *
     * @return string
     */

    public function search(): string
    {
        $args = [
            'parent' => get_the_ID(),
            'submit' => 'Search'
        ];

        return sprintf(
            '<div class="search wp-block-moj-search">%s</div>',
            $this->templatePartToVariable('template-parts/search/search-bar', null, $args)
        );
    }
}
