<?php

namespace MOJ\Justice;

use DOMDocument;
use DOMElement;
use WP_Document_Revisions;
use Timber\Timber;
use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * A class to hook into blocks to replace them with twig templates.
 *
 */
class Templates
{

    public array $allowedMimeTypes = [];
    public array $blocks = [
        'core/table',
        'core/image',
        'core/list',
    ];

    public function addHooks(): void
    {
        libxml_use_internal_errors(true);
        $this->allowedMimeTypes = wp_get_mime_types();
        add_action('render_block', [$this, 'replace_wp_blocks'], 10, 3);
    }

    public function replace_wp_blocks($block_content, $block)
    {
        if (in_array($block['blockName'], $this->blocks)) {
            $html = $block['innerHTML'];
            $doc = new DOMDocument();
            $doc->loadHTML($html);

            switch ($block['blockName']) {
                case 'core/paragraph':
                case 'core/table':
                    $this->render_links($doc);
                    break;
                default:
            }
            $block_content = $doc->saveHTML();
        }

        return $block_content;
    }

    public function convert_twig_template_to_dom_element($doc, $templates, $tagName = 'div', $params = []): DOMElement|null
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

    public function render_links($doc): void
    {
        $fileTemplate = ['partials/file-download.html.twig'];
        $linkTemplate = ['partials/link.html.twig'];
        $links = $doc->getElementsByTagName('a');
        foreach ($links as $link) {
            // If the link is a file use the file download template, otherwise skip as it will be styled as a link
            if (wp_check_filetype($link->getAttribute('href'), $this->allowedMimeTypes)['ext']) {
                $params = $this->get_file_download_params($link);
                $htmlDoc = $this->convert_twig_template_to_dom_element($doc, $fileTemplate, 'div', $params);
                $link->parentNode->replaceChild($htmlDoc, $link);
            } else {
                $params = $this->get_link_params($link);
                $htmlDoc = $this->convert_twig_template_to_dom_element($doc, $linkTemplate, 'a', $params);
                $link->parentNode->replaceChild($htmlDoc, $link);
            }
        }
    }

    public function get_link_params($node)
    {
        $label = null;
        $url = null;
        $newTab = false;

        if ($node instanceof DOMElement) {
            $label = $node->nodeValue;
            $url = $node->getAttribute('href');
            $newTab = $this->isExternal($url);
        }

        return [
            'label' => $label,
            'url' => $url,
            'newTab' => $newTab,
        ];
    }

    public function get_file_download_params($node)
    {
        $href = null;
        $filesize = null;
        $format = null;
        $language = null;
        $label = null;

        if ($node instanceof DOMElement) {
            $href = $node->getAttribute('href');
            $slug = basename(untrailingslashit($href));

            $label = $node->nodeValue;
            $document = new WP_Document_Revisions;
            $upload_dir = $document->document_upload_dir();
            $postId = url_to_postid('/documents/' . $slug);

            if ($document->verify_post_type($postId)) {
                $post = $document->get_document($postId);
                $format = $document->get_file_type($postId);
                $year_month = str_replace('-', '/', substr($post->post_date, 0, 7));
                $document_url = "{$upload_dir}/{$year_month}/{$post->post_title}{$format}";
            } else {
                $postId = url_to_postid($slug);
                $post = get_post($postId);
                $format = get_post_mime_type($postId);
                $year_month = str_replace('-', '/', substr($post->post_date, 0, 7));
                $document_url = "{$upload_dir}/{$year_month}/{$slug}";
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

    public function isExternal($url)
    {
        $components = parse_url($url);
        return !empty($components['host']) && strcasecmp($components['host'], $_SERVER['HTTP_HOST']);
    }
}