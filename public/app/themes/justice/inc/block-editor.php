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
        add_filter('the_content', [$this, 'formatMojAnchor']);
        add_filter('allowed_block_types_all', [$this, 'filterAllowedBlockTypes'], 10, 0);
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

    /**
     * Format the moj-anchor anchor.
     *
     * This is rich text formatting that is added to the block editor via a JS.
     * In the editor, a single space is used for compatibility, and so that the anchor can be selected.
     * The space is removed when the content is rendered, so as not to break the layout.
     *
     * @param string $content
     * @return string
     */

    public function formatMojAnchor($content)
    {

        // Match all a tags class of moj-anchor and a single space.
        $pattern = '/(<a[^>]*class="moj-anchor"[^>]*>) (<\/a>)/';
        // Remove the single space.
        $replacement = '$1$2';
        $content = preg_replace($pattern, $replacement, $content);

        return $content;
    }


    /**
     * Filters the list of allowed block types in the block editor.
     *
     * This function restricts the available block types to a predefined list.
     *
     * @return array The array of allowed block types.
     */
    public function filterAllowedBlockTypes()
    {
        return  [
            'core/footnotes',
            'core/heading',
            'core/image',
            'core/list',
            'core/list-item',
            'core/paragraph',
            'core/table',
            'moj/inline-menu',
            'moj/search',
            'moj/to-the-top',
            'simple-definition-list-blocks/details',
            'simple-definition-list-blocks/list',
            'simple-definition-list-blocks/term',
        ];
    }
}
