<?php

// First we need to load the composer autoloader, so we can use WP Mock
require_once dirname(__DIR__, 2).'/vendor/autoload.php';

// Bootstrap WP_Mock to initialize built-in features
WP_Mock::bootstrap();

global $theme_root_dir;

// Optional step
// If your project does not use autoloading via Composer, include your files now
require_once $theme_root_dir . '/inc/admin.php';
require_once $theme_root_dir . '/inc/block-editor.php';
require_once $theme_root_dir . '/inc/content-quality/issues/anchor.php';
require_once $theme_root_dir . '/inc/content-quality/issues/email-href.php';
require_once $theme_root_dir . '/inc/content-quality/issues/email-text.php';
require_once $theme_root_dir . '/inc/content-quality/issues/empty-heading.php';
require_once $theme_root_dir . '/inc/content-quality/issues/incomplete-thead.php';
require_once $theme_root_dir . '/inc/content-quality/issues/thead.php';
require_once $theme_root_dir . '/inc/post-meta/post-meta.php';
