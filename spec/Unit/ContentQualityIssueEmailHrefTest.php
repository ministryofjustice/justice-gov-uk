<?php

namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\ContentQualityIssueEmailHref;
use WP_Mock;

final class ContentQualityIssueEmailHrefTest extends \Codeception\Test\Unit
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

    public function testGetEmailFromHref(): void
    {
        $instance = ContentQualityIssueEmailHref::class;

        // Test various values that should return null
        $this->assertSame([], $instance::getEmailsFromHref(''));
        $this->assertSame([], $instance::getEmailsFromHref('mailto'));
        $this->assertSame([], $instance::getEmailsFromHref('https://example.com'));

        // // Test invalid email links
        $this->assertSame([false], $instance::getEmailsFromHref('mailto:'));
        $this->assertSame([false], $instance::getEmailsFromHref('mailto:invalid-email'));
        $this->assertSame([false], $instance::getEmailsFromHref('mailto:invalid-email@'));
        $this->assertSame([false], $instance::getEmailsFromHref('mailto:@example.com'));
        $this->assertSame([false], $instance::getEmailsFromHref('mailto:invalid-email@.com'));
        $this->assertSame([false], $instance::getEmailsFromHref('mailto:invalid-email@com'));
        $this->assertSame([false, false], $instance::getEmailsFromHref('mailto:invalid-email@com,another-invalid-email@com'));

        // Test valid percent encoded email links
        $this->assertSame(['web.comments@justice.gsi.gov.uk'], $instance::getEmailsFromHref('mailto:%77%65%62%2E%63%6F%6D%6D%65%6E%74%73%40%6A%75%73%74%69%63%65%2E%67%73%69%2E%67%6F%76%2E%75%6B'));

        // Test a valid email link
        $this->assertSame(['test@test.com'], $instance::getEmailsFromHref('mailto:test@test.com'));
        $this->assertSame(['Test_Testing@test.com'], $instance::getEmailsFromHref('mailto:Test_Testing@test.com'));
        $this->assertSame(['test@test.com'], $instance::getEmailsFromHref('mailto:test@test.com?subject=Test'));

        // Test multiple emails in the href
        $this->assertSame(['test@test.com', 'test2@example.com'], $instance::getEmailsFromHref('mailto:test@test.com,test2@example.com?subject=Test'));

        // Test an email link with extra spaces
        $this->assertSame(['test@test.com'], $instance::getEmailsFromHref('mailto: test@test.com '));
        $this->assertSame(['test@test.com', 'test2@example.com'], $instance::getEmailsFromHref('mailto: test@test.com ,test2@example.com '));

        // Test an invalid email format
        $this->assertSame([false], $instance::getEmailsFromHref('mailto:testtest.com'));
        $this->assertSame([false, 'valid@test.com'], $instance::getEmailsFromHref('mailto:testtest.com,valid@test.com'));
    }

    public function testGetInvalidEmailsFromContent(): void
    {
        $instance = ContentQualityIssueEmailHref::class;

        // Test content with a poorly formatted email link
        $content = '<p>Contact us at <a href="mailto:%77%65%62%2E%63%6F%6D%6D%65">Email Us</a></p>';
        $this->assertSame(1, $instance::getInvalidEmailsFromContent($content));

        // Test content with multiple emails and valid text content
        $content = '<p>Contact us at <a href="mailto:test@example.com,test2@example.com">test@example.com</a></p>';
        $this->assertSame(0, $instance::getInvalidEmailsFromContent($content));


        // Test long content with multiple email links
        $content = '<p>Contact us at <a href="mailto:test@example.com">test@example.com</a> ' .
            'and <a href="mailto:test@test.com">email us</a> for more information ' .
            'and <a href="mailto:">email us</a> for more information ' .
            'and <a href="mailto:test">email us</a> </p>';
        $this->assertSame(2, $instance::getInvalidEmailsFromContent($content));
    }
}
