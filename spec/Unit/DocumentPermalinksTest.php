<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\DocumentPermalinks;
use WP_Mock;

final class DocumentPermalinksTest extends \Codeception\Test\Unit
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

    

    public function testAddOrIncreaseSuffix(): void
    {
        // Test with no suffix
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test'), 'test-2');

        // Test with tailing dash
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test-'), 'test--2');

        // Test with a suffix of 0
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test-0'), 'test-0-2');
        // Test with a suffix of 00
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test-00'), 'test-00-2');
        // Test with a suffix of 003
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test-003'), 'test-003-2');

        // Test with a number without a dash
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test1'), 'test1-2');

        // Test with a suffix of 1
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test-1'), 'test-1-2');

        // Test with a suffix of 2
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test-2'), 'test-3');
        // Multiple digits should be handled correctly
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test-101'), 'test-102');
        // Multiple dashes should be handled correctly
        $this->assertEquals(DocumentPermalinks::addOrIncreaseSuffix('test-101-3'), 'test-101-4');
    }
}
