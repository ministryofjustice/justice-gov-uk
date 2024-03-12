<?php

/**
 * Prevent the document upload directory from being changed.
 * This can't be in the theme directory because it's loaded too late.
 */

add_filter('pre_site_option_document_upload_directory', fn () => '/var/www/html/public/app/uploads');
