<?php

namespace MOJ\Justice;

use DOMElement;

defined('ABSPATH') || exit;

class TemplateLinks
{
    // Only use the extensions that we expect otherwise use the standard link component
    const array ALLOWED_EXTENSIONS = [
        'doc',
        'pdf',
        'ppt',
        'zip',
        'xls',
        'xlsx',
    ];

    public Documents $documents;
    public Content $content;

    public function __construct()
    {
        $this->documents = new Documents();
        $this->content = new Content();
    }

    public function getLinkParamsFromNode(\DOMElement $node): array|null
    {
        return $this->getLinkParams(
            $node->getAttribute('href'),
            $node->nodeValue,
            $node->getAttribute('id'),
            $node->getAttribute('target')
        );
    }

    public function getLinkParams(
        string|null $url,
        string|null $label = null,
        string|null  $id = null,
        string|null $target = null
    ): array|null {
        if (!$url) {
            // If the href isn't set, return an empty array
            return null;
        }

        $format = pathinfo($url, PATHINFO_EXTENSION);
        $external = $this->content->isExternal($url);

        if (in_array($format, SELF::ALLOWED_EXTENSIONS) && !$external) {
            // We are dealing with an internal download file
            return $this->getFileDownloadParams($url, $format, $label, $id);
        }

        return $this->geStandardLinkParams($url, $label, $id, $target);
    }



    public function geStandardLinkParams($url, $label = null, $id = null, $target = null): array
    {
        // Determine properties based upon the URL and label
        $external = $this->content->isExternal($url);
        // If the URL is external, we assume it should open in a new tab
        $newTab = $external || $target === '_blank';
        // If the label is not provided, we use the filename as the label
        if (!$label) {
            $label = pathinfo($url, PATHINFO_FILENAME);
        }
        // If the label already has new tab/window then don't repeat it
        $manualNewTabText = (str_contains($label, 'new tab') || str_contains($label, 'new window'));

        return [
            // Pass the ID, unmodified.
            'id' => $id,
            'external' => $external,
            'label' => $label,
            'url' => $url,
            'newTab' => $newTab,
            'manualNewTabText' => $manualNewTabText,
        ];
    }


    public function getFileDownloadParams($url, $format, $label = null, $id = null): array
    {
        $label = $label ? trim($label) : null;

        // Get the document ID from the link
        $post_id = $this->documents->getDocumentIdByUrl($url);

        // If the label is empty, try to get it from the post title
        if (empty($label) && $post_id) {
            $label = get_the_title($post_id);
        }

        // If the label is still empty, use the filename from the URL
        if (empty($label)) {
            $label = pathinfo($url, PATHINFO_FILENAME);
        }

        $filesize = $this->content->getFormattedFilesize($post_id);

        $format = strtoupper(ltrim($format, '.'));

        return [
            'format' => $format,
            'filesize' => $filesize,
            'filename' => $label,
            'link' => $url,
            'language' => null,
            'id' => $id,
        ];
    }
}
