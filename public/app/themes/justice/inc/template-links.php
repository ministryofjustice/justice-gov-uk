<?php

namespace MOJ\Justice;

use DOMElement;

defined('ABSPATH') || exit;

class TemplateLinks
{
    // Only use the extensions that we expect otherwise use the standard link component
    const array ALLOWED_EXTENSIONS = [
        'doc',
        'docx',
        'pdf',
        'ppt',
        'pptx',
        'xls',
        'xlsx',
        'zip',
    ];

    public Documents $documents;
    public Content $content;

    public function __construct()
    {
        $this->documents = new Documents();
        $this->content = new Content();
    }

    /**
     * Get the link parameters from a DOMElement.
     *
     * This is a wrapper around getLinkParams to extract the parameters from a DOMElement.
     *
     * @param DOMElement $node The DOMElement to extract parameters from.
     * @return array|null The link parameters, or null if the href is not set.
     */
    public function getLinkParamsFromNode(\DOMElement $node): array|null
    {
        return $this->getLinkParams(
            $node->getAttribute('href'),
            $node->nodeValue,
            $node->getAttribute('id'),
            $node->getAttribute('target')
        );
    }


    /**
     * Get the link parameters based on the URL, label, ID, and target.
     * 
     * This method determines if the link is a file download or a standard link,
     * and returns the appropriate parameters for rendering.
     * 
     * @param string|null $url The URL of the link.
     */
    public function getLinkParams(
        string|null $url,
        string|null $label = null,
        string|null  $id = null,
        string|null $target = null
    ): array|null {
        $format = pathinfo($url, PATHINFO_EXTENSION);
        $external = $this->content->isExternal($url);

        if (in_array($format, SELF::ALLOWED_EXTENSIONS) && !$external) {
            // We are dealing with an internal download file
            return $this->getFileDownloadParams($url, $label, $id);
        }

        return $this->getStandardLinkParams($url, $label, $id, $target);
    }


    /**
     * Get the parameters for a standard link.
     *
     * This method determines the parameters for a standard link based on the URL, label, ID, and target.
     * It checks if the link is external, whether it should open in a new tab,
     * and whether the label already contains text indicating it opens in a new tab or window.
     * 
     * @param string|null $url The URL of the link.
     * @param string|null $label The label for the link.
     * @param string|null $id The ID of the link.
     * @param string|null $target The target attribute of the link.
     * @return array The parameters for the standard link.
     */
    public function getStandardLinkParams($url, $label = null, $id = null, $target = null): array
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


    /**
     * Get the parameters for a file download link.
     *
     * This method determines the parameters for a file download link based on the URL, label, and ID.
     * It extracts the file format, calculates the file size, and retrieves the document ID.
     * If the label is not provided, it uses the filename from the URL.
     * 
     * @param string $url The URL of the file.
     * @param string|null $label The label for the file download link.
     * @param string|null $id The ID of the file download link.
     * @return array The parameters for the file download link.
     */
    public function getFileDownloadParams($url, $label = null, $id = null): array
    {
        $format = pathinfo($url, PATHINFO_EXTENSION);

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

        return [
            // Pass the ID, unmodified.
            'id' => $id,
            'format' => strtoupper($format),
            'filesize' => $this->content->getFormattedFilesize($post_id),
            'filename' => $label,
            'link' => $url,
            'language' => null,
        ];
    }
}
