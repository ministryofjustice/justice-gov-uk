<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * Simple Definition List Blocks
 *
 * This class is responsible for registering the blocks for the Simple Definition List Blocks plugin.
 * It means that the plugin in the public/app/plugins directory is not responsible for registering the blocks.
 * By running the php code related to that block in here, we minimise the attack surface of the plugin.
 *
 * @see themes/justice/inc/simple-definition-list-blocks.php
 * @see themes/justice/src/components/simple-definition-list-blocks/block-editor.js
 */

class SimpleDefinitionsListBlocks
{

    public static $pluginName = 'simple-definition-list-blocks';
    public static $pluginLongName = 'simple-definition-list-blocks/simple-definition-list-blocks.php';
    public static $blocksDir = WP_PLUGIN_DIR . '/simple-definition-list-blocks/build/blocks';
    public static $info = ' <strong>^ Intentionally deactivated by CDPT. See: themes/justice/inc/simple-definition-list-blocks.php </strong>';

    public function __construct()
    {
        add_filter('all_plugins', [$this, 'updatePluginDescription']);
        add_action('init', [$this, 'loadTextDomain']);
        add_action('init', [$this, 'registerBlocks']);
    }

    /**
     * Update the plugin description in Dashboard > Plugins.
     *
     * @param array $plugins
     * @return array
     */

    public function updatePluginDescription($plugins) : array
    {
        if (isset($plugins[$this::$pluginLongName])) {
            $plugins[$this::$pluginLongName]['Name'] .= ' ^';
            $plugins[$this::$pluginLongName]['Description'] .= $this::$info;
        }

        return $plugins;
    }

    public function loadTextDomain()
    {
        load_plugin_textdomain('simple-definition-list-blocks', false, dirname($this::$pluginName) . '/languages');
    }

    public function registerBlocks()
    {
        register_block_type($this::$blocksDir . '/definition-details');
        register_block_type($this::$blocksDir . '/definition-list');
        register_block_type($this::$blocksDir . '/definition-term');
    }
}
