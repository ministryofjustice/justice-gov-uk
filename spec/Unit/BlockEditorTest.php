<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use Codeception\Attribute\Depends;
use MOJ\Justice\BlockEditor;
use MOJ\Justice\PostMeta;
use WP_Mock;

final class BlockEditorTest extends \Codeception\Test\Unit
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
        $block_editor = new BlockEditor();

        WP_Mock::expectActionAdded('init', [$block_editor, 'registerBlocks']);

        $block_editor->addHooks();
    }

    public function testRegisterBlocks(): void
    {

        $block_editor = new BlockEditor();

        WP_Mock::userFunction('register_block_type', [
            'times' => 1,
            'args' => ['moj/inline-menu', ['render_callback' => [$block_editor, 'inlineMenu']]],
        ]);

        $block_editor = new BlockEditor();
        $block_editor->registerBlocks();
    }

    public function testTemplatePartToVariable(): void
    {
        $block_editor = new BlockEditor();

        $input_args = ['title' => 'Test title'];

        WP_Mock::userFunction('get_template_part', [
            'times' => 1,
            'args' => ['slug', 'name', $input_args],
            'return' => fn ($_slug, $_name, $args) => require 'mock-template-part.php',
        ]);

        $result = $block_editor->templatePartToVariable('slug', 'name', $input_args);
        $this->assertEquals("<h1>Test title</h1>\n", $result);
    }

    #[Depends('testTemplatePartToVariable', 'Tests\Unit\PostMetaTest:testGetShortTitle')]
    public function testInlineMenu(): void
    {

        $block_editor = new BlockEditor();

        WP_Mock::userFunction('get_the_ID', [
            'times' => 2,
            'return' => 1,
        ]);

        WP_Mock::userFunction('get_pages', [
            'times' => 1,
            'args' => ['parent=1&sort_column=menu_order'],
            'return' => [
                (object) ['ID' => 2],
                (object) ['ID' => 3],
            ],
        ]);

        WP_Mock::userFunction('get_the_title', [
            'times' => 1,
            'args' => [2],
            'return' => 'Test title 2',
        ]);

        WP_Mock::userFunction('get_the_title', [
            'times' => 1,
            'args' => [3],
            'return' => 'Test title 3',
        ]);

        WP_Mock::userFunction('get_permalink', [
            'times' => 1,
            'args' => [2],
            'return' => 'http://example.com/2',
        ]);

        WP_Mock::userFunction('get_permalink', [
            'times' => 1,
            'args' => [3],
            'return' => 'http://example.com/3',
        ]);

        WP_Mock::userFunction('get_template_part', [
            'times' => 1,
            'return' => function ($_slug, $_name, $args) {
                 require __DIR__ . '/../../public/app/themes/justice/template-parts/common/inline-list.php';
            },
        ]);

        $result = $block_editor->inlineMenu();

        $minified_result = trim(preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $result));
        $expected_result = '<ul class="inline-list"> <li> <a href="http://example.com/2">Test title 2</a> </li> <li> <a href="http://example.com/3">Test title 3</a> </li> </ul>';

        $this->assertEquals($expected_result, $minified_result);
    }
}
