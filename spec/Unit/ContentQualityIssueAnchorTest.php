<?php

namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\ContentQualityIssueAnchor;
use WP_Mock;

final class ContentQualityIssueAnchorTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;


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

    public function testGetAnchorsFromContent(): void
    {
        $instance = new ContentQualityIssueAnchor();

        $this->assertEquals([], $instance->getAnchorsFromContent(''));
        $this->assertEquals([], $instance->getAnchorsFromContent('<p>Some text with an <a href="#top">anchor</a>.</p>'));
        $this->assertEquals(['section1'], $instance->getAnchorsFromContent('<p>Some text with an <a href="#section1">anchor</a>.</p>'));
    }

    public function testContentHasElementWithId(): void
    {
        $instance = new ContentQualityIssueAnchor();

        $this->assertTrue($instance->contentHasElementWithId('<div id="test">Test</div>', 'test'));
        $this->assertTrue($instance->contentHasElementWithId("<div id='test'>Test</div>", 'test'));
        $this->assertFalse($instance->contentHasElementWithId('<div>Test</div>', 'test'));
        $this->assertFalse($instance->contentHasElementWithId('', 'test'));
    }
}
