<?php

namespace MOJ\Justice;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use WP_Document_Revisions;
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

    public array $allowedMimeTypes = [];

    public array $blocks = [
        'core/paragraph',
        'core/table',
        'core/list-item',
        'moj/to-the-top'
    ];

    public function __construct()
    {
        libxml_use_internal_errors(true);
        $this->allowedMimeTypes = wp_get_mime_types();
        $this->addHooks();
    }

    public function addHooks(): void
    {
        add_action('render_block', [$this, 'replaceWordpressBlocks'], 10, 2);
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
        // Only target certain blocks
        if (in_array($block['blockName'], $this->blocks)) {
            $html = $block['innerHTML'];
            $doc = new DOMDocument();
            // Fix odd loading of special characters (see https://php.watch/versions/8.2/mbstring-qprint-base64-uuencode-html-entities-deprecated#html)
            $doc->loadHTML(htmlspecialchars_decode(htmlentities($html)));

            switch ($block['blockName']) {
                case 'core/paragraph':
                case 'core/list-item':
                    $this->renderLinks($doc);
                    break;
                case 'core/table':
                case 'moj/to-the-top':
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
        $htmlDoc->loadHTML($template);
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
            // If the link is a file use the file download template, otherwise use the link template
            if (wp_check_filetype($link->getAttribute('href'), $this->allowedMimeTypes)['ext']) {
                $params = $this->getFileDownloadParams($link);
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $fileTemplate, 'div', $params);
            } else if ($link->getAttribute('class') === 'to-the-top') {
                $params = $this->getLinkParams($link);
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $toTheTopTemplate, 'a', $params);
            } else {
                $params = $this->getLinkParams($link);
                $htmlDoc = $this->convertTwigTemplateToDomElement($doc, $linkTemplate, 'a', $params);
            }
            $link->parentNode->replaceChild($htmlDoc, $link);
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
        $content = new Content();
        $label = null;
        $url = null;
        $newTab = false;

        if ($node instanceof DOMElement) {
            $label = $node->nodeValue;
            $url = $node->getAttribute('href');
            $newTab = $content->isExternal($url);
        }

        return [
            'label' => $label,
            'url' => $url,
            'newTab' => $newTab,
        ];
    }

    /**
     * Gets the required parameters for file download links
     *
     * @param DOMNode $node The DOMNode containing the link information
     *
     * @return array An array of parameters to pass to the link template
     */
    public function getFileDownloadParams(DOMNode $node): array
    {
        $href = null;
        $filesize = null;
        $format = null;
        $language = null;
        $label = null;
        $document_url = null;

        if ($node instanceof DOMElement) {
            $href = $node->getAttribute('href');
            // Get the slug from the link
            $slug = basename(untrailingslashit($href));

            $label = $node->nodeValue;
            // Init a WP_Document_Revisions class so that we can use document specific functions
            $document = new WP_Document_Revisions;
            $upload_dir = $document->document_upload_dir();
            $postId = url_to_postid('/documents/' . $slug);

            // If the post has a post_type of document get the format and URL using the document_revisions class functions
            if ($document->verify_post_type($postId)) {
                $post = $document->get_document($postId);
                $format = $document->get_file_type($postId);
                $year_month = str_replace('-', '/', substr($post->post_date, 0, 7));
                $document_url = "{$upload_dir}/{$year_month}/{$post->post_title}{$format}";
            } else {
                // Otherwise get the details using the WP base functions
                $postId = url_to_postid($slug);
                $post = get_post($postId);
                // But check that the post exists first
                if ($post) {
                    $format = get_post_mime_type($postId);
                    $year_month = str_replace('-', '/', substr($post->post_date, 0, 7));
                    $document_url = "{$upload_dir}/{$year_month}/{$slug}";
                }
            }
            if (file_exists($document_url)) {
                $filesize = size_format(wp_filesize($document_url));
                // Language is not currently available. We will have to add a new field to the document content type if this is required.
                $language = '';
            }

            $label = $label ?? get_the_title($post);
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
