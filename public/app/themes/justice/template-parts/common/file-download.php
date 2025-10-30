<?php

/*
 * A file (pdf) for download
 *
 * Available variables:
 *   - format: 'PDF'|'ZIP'|'DOC' The file format
 *   - url: string The link to the file
 *   - label: string The name of the file
 *   - filesize: string The size of the file
 *   - language: string The language of the file if different to the language of the page
 *
 * Example usage:
 *   get_template_part('common/file-download', null, [
 *     'format' => 'PDF',
 *     'url' => '#',
 *     'label' => 'User manual',
 *     'filesize' => '120 KbB',
 *     'language' => 'Welsh'
 *   ]);
 */

defined('ABSPATH') || exit;

if (empty($args['format'])) {
    // Fallback to the links template part
    get_template_part('template-parts/common/link', null, $args);
    return;
}

$defaults = [
    'language' => null,
    'format' => null,
    'filesize' => null,
];

$args = array_merge($defaults, $args);

printf('<a class="file-download" href="%s">', esc_url($args['url']));
printf('<i class="file-download__icon icon-%s--em" aria-hidden="true"></i>', strtolower(esc_attr($args['format'] ?? '')));
print '<span class="file-download__prefix visually-hidden">Download</span>';
print '<span class="file-download__text">';

print esc_html($args['label']);

print '<br><small>';
if ($args['format']) {
    $parts = [
        $args['language'],
        $args['format'],
        $args['filesize']
    ];
    $parts = array_filter($parts); // Remove any null or empty values
    $esc_parts = array_map('esc_html', $parts);
    printf("(%s)", implode(', ', $esc_parts));
}
print '</small>';
print '</span>';
print '</a>';
