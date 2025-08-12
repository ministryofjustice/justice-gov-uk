<?php

namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\ContentQualityIssueEmailText;
use WP_Mock;

final class ContentQualityIssueEmailTextTest extends \Codeception\Test\Unit
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

    public function testGetInaccessibleEmailLinksFromContent(): void
    {
        $instance = ContentQualityIssueEmailText::class;

        // Test empty content
        $this->assertSame(0, $instance::getInaccessibleEmailLinksFromContent(''));

        // Test content without email links
        $this->assertSame(0, $instance::getInaccessibleEmailLinksFromContent('<p>No email links here.</p>'));

        // Test content with a correctly formatted email link
        $content = '<p>Contact us at <a href="mailto:test@example.com">test@example.com</a></p>';
        $this->assertSame(0, $instance::getInaccessibleEmailLinksFromContent($content));

        // Test content with an invalid text content
        $content = '<p>Contact us at <a href="mailto:test@example.com">Email Us</a></p>';
        $this->assertSame(1, $instance::getInaccessibleEmailLinksFromContent($content));


        // Test content with multiple emails and valid text content
        $content = '<p>Contact us at <a href="mailto:test@example.com,test2@example.com">test@example.com</a></p>';
        $this->assertSame(0, $instance::getInaccessibleEmailLinksFromContent($content));

        // Test content with multiple emails and invalid text content
        $content = '<p>Contact us at <a href="mailto:test@example.com,test2@example.com">Email Us</a></p>';
        $this->assertSame(1, $instance::getInaccessibleEmailLinksFromContent($content));

        // Test long content with multiple email links
        $content = '<p>Contact us at <a href="mailto:test@example.com">test@example.com</a> ' .
            'and <a href="mailto:test@test.com">email us</a> for more information ' .
            'and <a href="mailto:test">email us</a> </p>';
        $this->assertSame(2, $instance::getInaccessibleEmailLinksFromContent($content));
    }
}
