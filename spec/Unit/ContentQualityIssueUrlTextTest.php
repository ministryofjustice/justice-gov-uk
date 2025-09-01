<?php

namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\ContentQualityIssueURLText;
use WP_Mock;

final class ContentQualityIssueURLTextTest extends \Codeception\Test\Unit
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

    public function testGetInaccessibleUrlLinksFromContent(): void
    {
        $instance = ContentQualityIssueURLText::class;

        // Test empty content
        $this->assertSame(0, $instance::getInaccessibleUrlLinksFromContent(''));

        // Test content without email links
        $this->assertSame(0, $instance::getInaccessibleUrlLinksFromContent('<p>No URL links here.</p>'));

        // Test content with a correctly formatted URL link
        $content = '<p>... via the Legislation website at:  <a href="http://www.legislation.gov.uk/id/uksi/2021/196">The Civil Procedure (Amendment No. 2) Rules 2021</a></p>';
        $this->assertSame(0, $instance::getInaccessibleUrlLinksFromContent($content));

        // Test content with an invalid text content
        $content = '<p>... via the Legislation website at:  <a href="http://www.legislation.gov.uk/id/uksi/2021/196">http://www.legislation.gov.uk/id/uksi/2021/196</a></p>';
        $this->assertSame(1, $instance::getInaccessibleUrlLinksFromContent($content));

        // Test content with multiple links and valid text content
        $content = '<p><a href="https://example.com">Example label</a> ... <a href="https://example2.com">Example label 2</a></p>';
        $this->assertSame(0, $instance::getInaccessibleUrlLinksFromContent($content));

        // Test content with multiple links and invalid text content
        $content = '<p><a href="https://example.com">https://example.com</a> ... <a href="https://example2.com">Example label 2</a></p>';
        $this->assertSame(1, $instance::getInaccessibleUrlLinksFromContent($content));

        // Test long content with multiple email links
        $content = '<p>Contact us at <a href="https://example.com">https://example.com</a> ' .
            'and <a href="https://example2.com">https://example2.com</a> ' .
            'and <a href="https://example3.com">Example label 3</a> </p>';
        $this->assertSame(2, $instance::getInaccessibleUrlLinksFromContent($content));

        // A URL at the start of a paragraph should not be counted
        $content = '<p>https://example.com is a link.</p>';
        $this->assertSame(0, $instance::getInaccessibleUrlLinksFromContent($content));
    }
}
