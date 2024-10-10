<?php

namespace MOJ\Justice;

use Timber;

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
        add_filter('the_content', [$this, 'formatBlocks']);
        add_filter('allowed_block_types_all', [$this, 'filterAllowedBlockTypes'], 10, 0);
        add_filter('block_editor_settings_all', [$this, 'customiseSettings']);
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
        $hiddenInputs =  [[
            'name' => 'parent',
            'value' => get_the_ID(),
        ]];

        $templates = ['partials/search-bar-block.html.twig'];

        $context = Timber::context([
            'variant' => 'main',
            'search' => [
                'id' => 'search-bar-embedded',
                'action' => '/search',
                'input'=> [
                    'labelHidden'=> true,
                    'label' => 'Search',
                    'id' => 'searchbar-embedded-input',
                    'name' => 's',
                    'hiddenInputs' => $hiddenInputs,
                ],
                'button' => [
                    'text' => 'Search',
                ]
            ]
        ]);
        return Timber::compile($templates, $context);
    }

    /**
     * Format the moj-anchor anchor and footnotes blocks.
     *
     * moj-anchor
     * This is rich text formatting that is added to the block editor via a JS.
     * In the editor, a single space is used for compatibility, and so that the anchor can be selected.
     * The space is removed when the content is rendered, so as not to break the layout.
     *
     * core/footnotes
     * Edit the unicode character ↩︎ to "Back to text", to match the legacy content.
     *
     * @param string $content
     * @return string
     */

    public function formatBlocks($content)
    {

        // Match all a tags class of moj-anchor and a single space.
        $moj_anchor_pattern = '/(<a[^>]*class="moj-anchor"[^>]*>) (<\/a>)/';
        // Remove the single space.
        $content = preg_replace($moj_anchor_pattern, '$1$2', $content);

        // Match all footnotes.
        $footnotes_pattern = '/(<a href="#[^"]*" aria-label="Jump to footnote reference \d+">)↩︎(<\/a>)/';
        // Replace the unicode character ↩︎ with "Back to text".
        $content = preg_replace($footnotes_pattern, '$1Back to text$2', $content);

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

    /**
     * Customise block editor settings.
     *
     * @param array $settings The block editor settings.
     * @return array The modified block editor settings.
     */

    public function customiseSettings($settings)
    {
        // Disable the Openverse media category.
        $settings['enableOpenverseMediaCategory'] = false;

        return $settings;
    }
}
