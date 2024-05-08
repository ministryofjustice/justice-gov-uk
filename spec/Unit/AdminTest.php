<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\Admin;
use WP_Mock;

final class AdminTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    protected $example_theme_url = 'http://example.com/wp-content/themes/justice';


    public function setUp(): void
    {
        parent::setUp();

        WP_Mock::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        WP_Mock::tearDown();
    }

    public function testAddHooks(): void
    {
        $admin = new Admin();

        WP_Mock::expectActionAdded('admin_enqueue_scripts', [$admin, 'enqueueStyles']);
        WP_Mock::expectActionAdded('admin_menu', [$admin, 'removeCustomizer'], 999);

        $admin->addHooks();
    }

    public function testEnqueueStyles(): void
    {
        WP_Mock::userFunction('get_template_directory_uri', [
            'times' => 2,
            'return' => $this->example_theme_url,
        ]);

        WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 1,
            'args' => ['justice-admin-style', $this->example_theme_url . '/dist/css/admin.min.css'],
        ]);

        WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 1,
            'args' => ['justice-editor-style', $this->example_theme_url . '/dist/css/editor.min.css'],
        ]);

        Admin::enqueueStyles();
    }

    public function testRemoveCustomizer(): void
    {
        $example_admin_url = 'http://example.com/wp/wp-admin/edit-tags.php';
        $example_admin_url_with_query = $example_admin_url . '?taxonomy=category';
        
        $_SERVER['REQUEST_URI'] = $example_admin_url_with_query;

        WP_Mock::userFunction('wp_removable_query_args', [
            'times' => 1,
            'return' => [
                // ...
                'taxonomy',
                // ...
            ],
        ]);

        WP_Mock::userFunction('wp_unslash', [
            'times' => 1,
            'args' => [$example_admin_url_with_query],
            'return' => $example_admin_url_with_query,
        ]);

        WP_Mock::userFunction('remove_query_arg', [
            'times' => 1,
            'args' => [['taxonomy'],  $example_admin_url_with_query],
            'return' =>  $example_admin_url,
        ]);

        WP_Mock::userFunction('add_query_arg', [
            'times' => 1,
            'args' => ['return', urlencode($example_admin_url), 'customize.php'],
            'return' => 'customize.php?return=%2Fwp%2Fwp-admin%2Findex.php',
        ]);

        WP_Mock::userFunction('remove_submenu_page', [
            'times' => 1,
            'args' => ['themes.php', 'customize.php?return=%2Fwp%2Fwp-admin%2Findex.php'],
        ]);

        Admin::removeCustomizer();
    }
}
