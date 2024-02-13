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
        add_action( 'admin_enqueue_scripts', array($this, 'enqueueStyles') );
        add_action( 'admin_enqueue_scripts', array($this, 'enqueueScripts') );
        // add_action('wp_before_admin_bar_render', array($this, 'editAdminBar'));
    }
    
    public static function enqueueStyles()
    {
        wp_enqueue_style( 'justice-admin-style', get_template_directory_uri() . '/dist/css/wp-admin-override.css' );
    }
    
    public static function enqueueScripts()
    {
        wp_enqueue_script('justice-admin', get_template_directory_uri() . '/dist/admin.min.js', [], false, true);
    }
    
    // public static function editAdminBar()
    // {
    //     global $wp_admin_bar;
    //     $wp_admin_bar->remove_menu('customize');
    // }
}