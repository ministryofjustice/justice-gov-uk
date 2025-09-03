<?php

namespace MOJ\Justice;

use DOMDocument;
use DOMNode;
use DOMXPath;
use Timber\Timber;
use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A class to hook into WP blocks and replace them with twig templates
 */

class Templates
{
    

    public array $blocks = [
        'core/paragraph',
        'core/table',
        'core/list',
        'moj/to-the-top'
    ];

    public Documents $documents;
    public Content $content;
    public TemplateLinks $links;

    public function __construct()
    {
        libxml_use_internal_errors(true);
        $this->documents = new Documents();
        $this->content = new Content();
        $this->links = new TemplateLinks();
    }

    public function addHooks(): void
    {
        add_action('render_block', [$this, 'replaceWordpressBlocks'], 10, 2);
        add_action('render_block', [$this, 'replaceDuplicateDownloadDetails'], 11, 1);
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

    /**
     * Replace a WP block with a twig template
     *
     * @param string $block_content A string representation of the block content
     * @param array $block A representative array of the block being rendered
     * @return string The modified block content as an HTML string
     *@see https://developer.wordpress.org/reference/classes/wp_block_parser_block/
     *
     */
    public function replaceWordpressBlocks(string $block_content, array $block): string
    {
        // Only target certain blocks and only run in the main loop on pages/posts
        if ((in_array($block['blockName'], $this->blocks)) && (is_single() || is_page()) && in_the_loop() && is_main_query()) {
            $html = $block['innerHTML'];

            if (!$html) {
                return $block_content;
            }

            $doc = new DOMDocument();
            $this->loadPartialHTML($doc, $html);

            switch ($block['blockName']) {
                case 'core/paragraph':
                case 'moj/to-the-top':
                    $this->renderLinks($doc);
                    break;
                case 'core/list':
                    $this->renderList($doc, $block['innerBlocks']);
                    break;
                case 'core/table':
                    $this->renderLinks($doc);
                    $this->addTableScopes($doc);
                    break;
                default:
            }
            $block_content = $doc->saveHTML();
        }

        return $block_content;
    }

    /**
     * Converts an HTML string to a DOMNode for use with DOMDocument
     *
     * @param DOMDocument $doc The DOMDocument that the html will be added to
     * @param array $templates An array of Twig templates to be rendered
     * @param array $params (optional) The parameters to pass to the Twig template
     *
     * @return DOMNode|bool The Twig template as a DOMNode or false if the conversion failed
     */
    public function convertTwigTemplateToDomElement(DOMDocument $doc, array $templates, array $params = []): DOMNode|bool
    {
        $htmlDoc = new DOMDocument();
        $context = Timber::context($params);
        $template = Timber::compile($templates, $context);
        $this->loadPartialHTML($htmlDoc, $template);
        $appended = null;
        try {
            $imported = $doc->importNode($htmlDoc->firstChild, true);
            $appended = $doc->appendChild($imported);
        } catch (Exception $ex) {
            error_log($ex->getMessage(), 0);
        }
        return $appended;
    }

    /**
     * Applies the file-download template to download links and the standard link template to others
     *
     * @param DOMDocument $doc The DOMDocument that the html will be added to
     *
     */
    public function renderLinks(DOMDocument $doc): void
    {
        $fileTemplate = ['partials/file-download.html.twig'];
        $linkTemplate = ['partials/link.html.twig'];
        $toTheTopTemplate = ['partials/to-the-top.html.twig'];
        $links = $doc->getElementsByTagName('a');
        foreach ($links as $link) {
            $params = $this->links->getLinkParamsFromNode($link);

            if (isset($params['format'])) {
                // The link is a file use the file download template, otherwise use the link template
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $fileTemplate, $params);
            } else if ($link->getAttribute('class') === 'to-the-top') {
                // Check if it has the 'to-the-top' class and use that template
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $toTheTopTemplate, $params);
            } else {
                // Otherwise default to the standard link template
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $linkTemplate, $params);
            }

            if ($htmlDoc) {
                $link->parentNode->replaceChild($htmlDoc, $link);
            }
        }
    }

    
    /**
     * Applies the navigation sections template to list elements with the "Horizontal & Border" style
     *
     * @param DOMDocument $doc The DOMDocument that the html will be added to
     * @param array $innerBlocks An array of list items
     *
     */
    public function renderList(DOMDocument $doc, $innerBlocks): void
    {
        $navigationTemplate = ['partials/navigation-sections.html.twig'];

        $lists = $doc->getElementsByTagName('ul');
        foreach ($lists as $list) {
            // If the list has the horizontal styling class (typo in 'page' is intentional, see global.css)
            if ($list->getAttribute('class') === 'is-style-pag-nav') {
                $links = [];
                // For each list element, get the label and href values
                foreach ($innerBlocks as $block) {
                    $blockDoc = new DOMDocument();
                    $this->loadPartialHTML($blockDoc, $block['innerHTML']);

                    $node = $blockDoc->getElementsByTagName('a')[0];
                    $links[] = $this->links->getLinkParamsFromNode($node);
                }
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $navigationTemplate, ['links' => $links]);
                $list->parentNode->replaceChild($htmlDoc, $list);
            } else {
                // Otherwise treat each block as a list element and render any links appropriately
                foreach ($innerBlocks as $block) {
                    $blockDoc = new DOMDocument();
                    $this->loadPartialHTML($blockDoc, $block['innerHTML']);
                    $this->renderLinks($blockDoc);
                    $node = $blockDoc->documentElement;
                    $imported = $doc->importNode($node, true);
                    $list->appendChild($imported);
                }
            }
        }
    }

    /**
     * Adds the correct scopes to table headers
     *
     * @param DOMDocument $doc The DOMDocument that the html will be added to
     *
     */

    public function addTableScopes(DOMDocument $doc): void
    {
        $xpath = new DOMXPath($doc);
        $head = $xpath->query('//thead/tr/th');
        $body = $xpath->query('//tbody/tr/th');
        foreach ($head as $node) {
            $node->setAttribute('scope', 'col');
        }
        foreach ($body as $node) {
            $node->setAttribute('scope', 'row');
        }
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
        // - `<!-- /.file-download --> </a> (PDF)` -> `</a>`
        $regex_pattern = '/<!-- \/\.file-download -->\v(\s*)?<\/a>\s{0,1}\(PDF\)/';
        return preg_replace($regex_pattern, '</a>', $block_content);
    }
}
