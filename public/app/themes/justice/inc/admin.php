<?php

/**
 * A php class to add admin functionality to the theme
 * Like enqueue the css and js files.
 */

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    die();
}

class Admin
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_menu', [$this, 'removeCustomizer'], 999);
        add_action('admin_head', [$this, 'removePublishPressUpsellBranding']);

    }

    public static function enqueueStyles()
    {
        wp_enqueue_style('justice-admin-style', get_template_directory_uri() . '/dist/css/editor.min.css');
    }

    public static function removeCustomizer()
    {
        // We need this because the submenu's link (key from the array too) will always be generated with the current SERVER_URI in mind.
        $customizer_url = add_query_arg('return', urlencode(remove_query_arg(wp_removable_query_args(), wp_unslash($_SERVER['REQUEST_URI']))), 'customize.php');
        remove_submenu_page('themes.php', $customizer_url);
    }

    public static function removePublishPressUpsellBranding()
    {
        echo '<style>
               .toplevel_page_revisionary-q {
                 li:last-of-type {
                   display: none;
                 }
               }
               .pp-version-notice-bold-purple {
                 display: none;
               }
               .pressshack-admin-wrapper {
                 .inner-sidebar {
                   display: none;
                 }
                 footer {
                   display: none;
                 }  
               }
               .ppseries-settings-body-content {
                 width: 100% !important;
               }
             </style>';
    }
}
