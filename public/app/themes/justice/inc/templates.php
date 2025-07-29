<?php

namespace MOJ\Justice;

use DOMDocument;
use DOMElement;
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
    // Only use the mime types that we expect otherwise use the standard link component
    public array $allowedMimeTypes = [
        'doc',
        'pdf',
        'ppt',
        'zip',
        'xls',
    ];

    public array $blocks = [
        'core/paragraph',
        'core/table',
        'core/list',
        'moj/to-the-top'
    ];

    public Documents $documents;
    public Content $content;

    public function __construct()
    {
        libxml_use_internal_errors(true);
        $this->addHooks();
        $this->documents = new Documents();
        $this->content = new Content();
    }

    public function addHooks(): void
    {
        add_action('render_block', [$this, 'replaceWordpressBlocks'], 10, 2);
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
        // $doc->encoding = 'UTF-8';
        // Prefixing the HTML with an XML declaration to ensure proper encoding handling
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
     * @param string $tagName (optional) The wrapper tag to find in the Twig template, e.g. a for a link component
     * @param array $params (optional) The parameters to pass to the Twig template
     *
     * @return DOMNode|bool The Twig template as a DOMNode or false if the conversion failed
     */
    public function convertTwigTemplateToDomElement(DOMDocument $doc, array $templates, string $tagName = 'div', array $params = []): DOMNode|bool
    {
        $htmlDoc = new DOMDocument();
        $context = Timber::context($params);
        $template = Timber::compile($templates, $context);
        $this->loadPartialHTML($htmlDoc, $template);
        $appended = null;
        try {
            $els = $htmlDoc->getElementsByTagName($tagName);
            $imported = $doc->importNode($els[0], true);
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
            $url = $link->getAttribute('href');
            $format = pathinfo($url, PATHINFO_EXTENSION);
            $external = $this->content->isExternal($url);

            if (!$link->getAttribute('href')) {
                // The href isn't set skip the loop and use the default node (needed for the anchor links in WP)
                continue;
            } else if (in_array($format, $this->allowedMimeTypes) && !$external) {
                // The link is a file use the file download template, otherwise use the link template
                $params = $this->getFileDownloadParams($link, $format);
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $fileTemplate, 'div', $params);
            } else if ($link->getAttribute('class') === 'to-the-top') {
                // Check if it has the 'to-the-top' class and use that template
                $params = $this->getLinkParams($link);
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $toTheTopTemplate, 'a', $params);
            } else {
                // Otherwise default to the standard link template
                $params = $this->getLinkParams($link);
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $linkTemplate, 'a', $params);
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
                    // $blockDoc->loadHTML(htmlspecialchars_decode(htmlentities($block['innerHTML'])), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                    $this->loadPartialHTML($blockDoc, $block['innerHTML']);

                    $node = $blockDoc->getElementsByTagName('a')[0];
                    $links[] = $this->getLinkParams($node);
                }
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $navigationTemplate, 'nav', ['links' => $links]);
                $list->parentNode->replaceChild($htmlDoc, $list);
            } else {
                // Otherwise treat each block as a list element and render any links appropriately
                foreach ($innerBlocks as $block) {
                    $blockDoc = new DOMDocument();
                    // $blockDoc->loadHTML(htmlspecialchars_decode(htmlentities($block['innerHTML'])), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
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
     * Gets the required parameters for links
     *
     * @param DOMNode $node The DOMNode containing the link information
     *
     * @return array An array of parameters to pass to the link template
     */
    public function getLinkParams(DOMNode $node): array
    {
        $label = null;
        $url = null;
        $id = null;
        $newTab = false;
        $manualNewTabText = false;

        if ($node instanceof DOMElement) {
            $url = $node->getAttribute('href');
            $id = $node->getAttribute('id');
            $label = $node->nodeValue ?: pathinfo($url, PATHINFO_FILENAME);
            $newTab = $this->content->isExternal($url);
            // If the label already has new tab/window then don't repeat it
            $manualNewTabText = (str_contains($label, 'new tab') || str_contains($label, 'new window'));
        }

        return [
            'label' => $label,
            'url' => $url,
            'newTab' => $newTab,
            'manualNewTabText' => $manualNewTabText,
            'id' => $id
        ];
    }

    /**
     * Gets the required parameters for file download links
     *
     * @param DOMNode $node The DOMNode containing the link information
     *
     * @return array An array of parameters to pass to the link template
     */
    public function getFileDownloadParams(DOMNode $node, $format): array
    {
        $href = null;
        $filesize = null;
        $language = null;
        $label = null;

        if ($node instanceof DOMElement) {
            $href = $node->getAttribute('href');
            $label = $node->nodeValue;

            // Get the document ID from the link
            $postId = $this->documents->getDocumentIdByUrl($href);

            $label = $label ?? get_the_title($postId);
            $filesize = $this->content->getFormattedFilesize($postId);
            $format = strtoupper(ltrim($format, '.'));
        }

        return [
            'format' => $format,
            'filesize' => $filesize,
            'filename' => $label,
            'link' => $href,
            'language' => $language,
        ];
    }
}
