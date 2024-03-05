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

    public function registerHooks()
    {
        add_action('init', [$this, 'registerBlocks']);
    }

    public function registerBlocks()
    {
        register_block_type('moj/inline-menu', array(
            'render_callback' => [$this, 'inlineMenu']
        ));
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
}
