<?php 

defined('ABSPATH') || exit;

$defaults = [
    'language' => null,
    'format' => null,
    'filesize' => null,
];

$args = array_merge($defaults, $args);

printf('<a class="file-download" href="%s">', esc_url($args['url']));
printf('<i class="file-download__icon icon-%s--em" aria-hidden="true"></i>', strtolower(esc_attr($args['format'])));
print '<span class="file-download__prefix visually-hidden">Download</span>';
print '<span class="file-download__text">';

print esc_html($args['filename']);

if ($args['format']) {
    $parts = [
        $args['language'],
        $args['format'],
        $args['filesize'],
    ];
    $parts = array_filter($parts); // Remove any null or empty values
    $esc_parts = array_map('esc_html', $parts);
    printf(' (%s)', implode(', ', $esc_parts));
}

print '</span>';
print '</a>';
