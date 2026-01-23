<?php

namespace MOJ\Justice;

use DOMDocument;
use DOMXPath;

use Roots\WPConfig\Config;
use MOJ\Justice\BlockEditor;
use MOJ\Justice\ContentLinks;

defined('ABSPATH') || exit;

/**
 * Actions and filters related to content.
 */

class Content
{

    public ContentLinks $links;

    /** @var string Prefix for content cache transients */
    const CACHE_PREFIX = 'justice_content_cache_';

    /** @var bool Whether to skip content caching */
    private static bool $skip_cache = false;

    public function __construct()
    {
        libxml_use_internal_errors(true);
        $this->addHooks();
    }

    public function addHooks(): void
    {
        // Filter the body class depending on content
        add_filter('body_class', [__CLASS__, 'addBodyClassIfContentContainsH1'], 25);

        // Filter the content to correctly render various elements
        add_filter('the_content', [__CLASS__, 'fixNationalArchiveLinks']);
        add_filter('wp_kses_allowed_html', [__CLASS__, 'customWpksesPostTags'], 10, 2);

        add_action('render_block_core/list-item', [$this, 'renderLinks'], 10, 2);
        add_action('render_block_core/paragraph', [$this, 'renderLinks'], 10, 2);
        add_action('render_block_core/table', [$this, 'renderLinks'], 10, 2);

        add_action('render_block_core/table', [$this, 'renderTables'], 10, 2);

        add_action('render_block_core/list', [$this, 'renderNavigationSection'], 15, 2);

        // Check transient / cache EARLY in the_content filter.
        // If it's present, we can skip all other processing.
        add_filter('the_content', [$this, 'checkContentCache'], 1);

        // Save transient / cache LATE in the_content filter,
        // after all other processing is done, and we have the final content to cache.
        add_filter('the_content', [__CLASS__, 'saveContentCache'], 999);

        // Clear the cache whenever an existing post is updated.
        add_action('wp_after_insert_post', [__CLASS__, 'invalidateCache']);
        // Clear the cache when a revision is published.
        add_action('revisionary_revision_published', [__CLASS__, 'invalidateCache']);
    }

    /**
     * Add a body class if the content contains an <h1> tag.
     *
     * This is used to add the 'rich-text-contains-h1' class to the body element,
     * which can be used to style the rich text component differently if it contains an <h1> tag.
     *
     * @param array $classes The existing body classes.
     * @return array The modified body classes.
     */
    public static function addBodyClassIfContentContainsH1($classes)
    {
        if ((is_single() || is_page()) && is_main_query()) {
            $content = get_the_content();
            if (strpos($content, '<h1') !== false) {
                return [...$classes, 'rich-text-contains-h1'];
            }
        }
        return $classes;
    }

    /**
     * Loads a string of HTML into a DOMDocument
     *
     * The string should be UTF-8 encoded, and it should be a partial HTML fragment,
     * i.e. it doesn't have a head, body, or doctype.
     *
     * @param DOMDocument $doc The DOMDocument that the html will be added to
     * @param string $html The HTML string to load into the DOMDocument
     * @return void
     */
    public function loadPartialHTML(DOMDocument &$doc, string $html): void
    {
        // Prefixing the HTML with an XML declaration to ensure proper encoding handling
        // Pass LIBXML_HTML_NOIMPLIED to avoid adding <html> and <body> tags.
        // Pass LIBXML_HTML_NODEFDTD to avoid adding a doctype declaration.
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        // Remove the XML declaration that was added
        $doc->removeChild($doc->firstChild);
    }


    public function renderLinks($block_content, $block)
    {
        if (!in_the_loop() || !is_main_query() || (!is_single() && !is_page())) {
            return $block_content;
        }

        if (empty($block['innerHTML'])) {
            return $block_content;
        }

        if (strpos($block['innerHTML'], '<a ') === false) {
            return $block_content;
        }

        $html = $block['innerHTML'];

        $doc = new \DOMDocument();
        $this->loadPartialHTML($doc, $html);

        $links = $doc->getElementsByTagName('a');

        $link_template = 'template-parts/common/link';
        $file_download_template = 'template-parts/common/file-download';

        foreach ($links as $link) {
            $args = ContentLinks::getLinkParamsFromNode($link);

            $template = !empty($args['format']) ? $file_download_template : $link_template;

            $new_html = BlockEditor::templatePartToVariable($template, null, $args);

            if (empty($new_html)) {
                continue;
            }

            // Add a marker after file-download elements.
            if (!empty($args['format'])) {
                $new_html .= "<!-- /.file-download -->";
            }

            // Replace the link's inner HTML with the new HTML
            $new_node = $doc->createDocumentFragment();
            $new_node->appendXML(trim($new_html));
            $link->parentNode->replaceChild($new_node, $link);
        }

        $block_content = $doc->saveHTML();

        return self::replaceDuplicateDownloadDetails($block_content);
    }


    /**
     * Replace duplicate "(PDF)" text following a file download block
     *
     * This is used to remove the duplicate "(PDF)" text that appears after the file download block.
     * It looks for the specific HTML comment that marks the end of the file download block and
     * replaces the "(PDF)" text that follows it with an empty string.
     *
     * @param string $block_content The content of the block to be processed
     * @return string The modified block content with duplicate "(PDF)" text removed
     */
    public static function replaceDuplicateDownloadDetails(string $block_content): string
    {
        // If the block content is empty, return it as is
        if (empty($block_content)) {
            return $block_content;
        }

        // Regex to replace the string:
        // - `<!-- /.file-download --> (PDF)` -> `</a>`
        $regex_pattern = '/<!-- \/\.file-download -->\v?(\s*)?\(PDF\)/';
        return preg_replace($regex_pattern, '</a>', $block_content);
    }


    /**
     * Adds the correct scopes to table headers
     *
     * This function modifies the table headers in the block content to ensure that they have the correct scope attributes.
     * It sets the scope attribute to "col" for table header cells in the table head
     * and to "row" for table header cells in the table body.
     *
     * @param string $block_content The content of the block to be processed
     *
     */
    public function renderTables(string $block_content, $block): string
    {
        if (!in_the_loop() || !is_main_query() || (!is_single() && !is_page())) {
            return $block_content;
        }

        if (empty($block['innerHTML'])) {
            return $block_content;
        }

        if (strpos($block['innerHTML'], '<th ') === false) {
            return $block_content;
        }

        $html = $block['innerHTML'];

        $doc = new \DOMDocument();
        $this->loadPartialHTML($doc, $html);

        $xpath = new DOMXPath($doc);
        $head = $xpath->query('//thead/tr/th');
        $body = $xpath->query('//tbody/tr/th');
        foreach ($head as $node) {
            $node->setAttribute('scope', 'col');
        }
        foreach ($body as $node) {
            $node->setAttribute('scope', 'row');
        }

        return $doc->saveHTML();
    }


    /**
     * Renders the navigation section block.
     *
     * This function processes the block content to extract links from list items
     * and renders them using a template part.
     *
     * @param string $block_content The content of the block to be processed
     * @param array $block The block data
     * @return string The rendered HTML for the navigation section
     */
    public function renderNavigationSection($block_content, $block)
    {
        if (!in_the_loop() || !is_main_query() || (!is_single() && !is_page())) {
            return $block_content;
        }

        if (($block['attrs']['className'] ?? '') !== 'is-style-pag-nav') {
            return $block_content;
        }

        $links = array_map([__class__, 'getLinkFroListItemBlock'], $block['innerBlocks']);

        return BlockEditor::templatePartToVariable('template-parts/nav/navigation-sections', null, [
            'links' => $links
        ]);
    }


    /**
     * Extracts the link and label from a list item block.
     *
     * This function uses a regular expression to find the link and label within the block's inner HTML.
     * It returns an associative array with 'url' and 'label' keys if successful, or null if not.
     *
     * @param array $block The block data
     * @return array|null An associative array with 'url' and 'label', or null if not a list item block
     */
    public static function getLinkFroListItemBlock($block)
    {
        if ('core/list-item' !== $block['blockName']) {
            return null; // Not a list item block
        }

        preg_match('/<a href="([^"]+)">([^<]+)<\/a>/', $block['innerHTML'], $matches);

        if (count($matches) !== 3) {
            return null; // Skip if the regex did not match
        }

        return [
            'url' => $matches[1],
            'label' => $matches[2],
        ];
    }

    /**
     * Filter the content to fix broken National Archives links.
     *
     * A fix to replace the dev/demo/stage URL with the www URL when pointing to national archive URLs.
     * This only runs on non-production environments.
     *
     * e.g. It replaces the broken link:
     * https://webarchive.nationalarchives.gov.uk/ukgwa/20211201113600/https://stage.justice.gov.uk/courts/procedure-rules/family/parts/part_02
     * with the working link:
     * https://webarchive.nationalarchives.gov.uk/ukgwa/20211201113600/https://www.justice.gov.uk/courts/procedure-rules/family/parts/part_02
     *
     * @param string $content
     * @return string
     */
    public static function fixNationalArchiveLinks($content)
    {

        if (Config::get('WP_ENVIRONMENT_TYPE') === 'staging') {
            // Match strings that start with https://webarchive.nationalarchives.gov.uk any amount words and / then ://stage
            $pattern = '/(https:\/\/webarchive\.nationalarchives\.gov\.uk)([\w\/]*)(:\/\/stage)/';
            return preg_replace($pattern, '$1$2://www', $content);
        }

        return $content;
    }




    /**
     * Customizes the allowed HTML tags for post content.
     *
     * @param array $tags The allowed HTML tags.
     * @param string $context The context in which the tags are being used.
     * @return array The modified allowed HTML tags.
     */
    public static function customWpksesPostTags($tags, $context)
    {

        if ('post' === $context) {
            // Allow the input tag, for
            $tags['input'] = array(
                'id'             => true,
                'class'          => true,
                'name'           => true,
                'type'     => true,
                'value' => true,
            );

            // Remove iframe tags to prevent embedding of external content
            if (isset($tags['iframe'])) {
                unset($tags['iframe']);
            }
        }

        return $tags;
    }

    /**
     * Check if we have cached processed content for this post.
     *
     * If cached content is found, it is returned immediately, skipping all further processing.
     *
     * @param string $content The original content.
     * @return string The processed content from the cache if available, otherwise the original content.
     */
    public function checkContentCache($content)
    {

        // Conditions for skipping the cache:
        // not a single page, non-main-query, or user is logged in.
        if (!is_singular('page') || !is_main_query() || is_user_logged_in()) {
            self::$skip_cache = true;
            return $content;
        }

        $cached = get_transient(self::CACHE_PREFIX . get_the_id());

        if ($cached !== false) {
            // We have cached content - skip all further processing.

            // Skip saving the cached result, back into the cache.
            // This value is checked in saveContentCache().
            self::$skip_cache = true;

            // Remove all the hooks ( except body_class filter ) - they don't need to run.
            remove_filter('the_content', [__CLASS__, 'fixNationalArchiveLinks']);
            remove_filter('wp_kses_allowed_html', [__CLASS__, 'customWpksesPostTags'], 10, 2);

            remove_action('render_block_core/list-item', [$this, 'renderLinks'], 10, 2);
            remove_action('render_block_core/paragraph', [$this, 'renderLinks'], 10, 2);
            remove_action('render_block_core/table', [$this, 'renderLinks'], 10, 2);

            remove_action('render_block_core/table', [$this, 'renderTables'], 10, 2);

            remove_action('render_block_core/list', [$this, 'renderNavigationSection'], 15, 2);

            return $cached;
        }


        return $content;
    }

    /**
     * Save processed content to cache.
     *
     * @param string $content The content to be cached.
     * @return string The unmodified content.
     */
    public static function saveContentCache($content)
    {

        // Check if we should skip caching
        if (self::$skip_cache) {
            return $content;
        }

        // Cache for 1 hour (3600 seconds)
        set_transient(self::CACHE_PREFIX . get_the_id(), $content, HOUR_IN_SECONDS);

        return $content;
    }

    /**
     * Invalidate cache when post is saved.
     * Hook this to 'save_post' action.
     *
     * @param int|WP_Post $post The post ID or post object.
     * @return void
     */
    public static function invalidateCache($post) :void
    {
        $post_id = is_object($post) ? $post->ID : $post;
        
        // Ensure we have a valid post ID
        if (empty($post_id)) {
            return;
        }

        // Only invalidate cache for 'page' post types
        if (get_post_type($post_id) !== 'page') {
            return;
        }

        delete_transient(self::CACHE_PREFIX . $post_id);
    }
}
