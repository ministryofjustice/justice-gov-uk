<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\Admin;
use WP_Mock;

final class AdminTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    protected $example_url = 'http://example.com/wp-content/themes/justice';

    public function setUp() : void
    {
        parent::setUp();

        WP_Mock::setUp();
    }

    public function tearDown() : void
    {
        parent::tearDown();

        WP_Mock::tearDown();
    }

    public function testAddHooks() : void
    {
        $admin = new Admin();

        // WP_Mock::expectActionAdded('admin_bar_menu', [$admin, 'editAdminBar']);
        WP_Mock::expectActionAdded('admin_enqueue_scripts', [$admin, 'enqueueStyles']);
        WP_Mock::expectActionAdded('admin_enqueue_scripts', [$admin, 'enqueueScripts']);
        
        $admin->addHooks();
    }

    public function testEnqueueStyles() : void
    {
        WP_Mock::userFunction('get_template_directory_uri', [
            'times' => 1,
            'return' => $this->example_url,
        ]);

        WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 1,
            'args' => ['justice-admin-style', $this->example_url . '/dist/css/wp-admin-override.css'],
        ]);

        Admin::enqueueStyles();
    }

    public function testEnqueueScripts() : void
    {
        WP_Mock::userFunction('get_template_directory_uri', [
            'times' => 1,
            'return' => $this->example_url,
        ]);

        WP_Mock::userFunction('wp_enqueue_script', [
            'times' => 1,
            'args' => ['justice-admin', $this->example_url . '/dist/admin.min.js', [], false, true],
        ]);

        Admin::enqueueScripts();
    }
}
