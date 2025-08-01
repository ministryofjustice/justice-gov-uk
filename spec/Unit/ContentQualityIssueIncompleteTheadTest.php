<?php

namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\ContentQualityIssueIncompleteThead;
use WP_Mock;

final class ContentQualityIssueIncompleteTheadTest extends \Codeception\Test\Unit
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

    public function testGetIncompleteTheadFromContent(): void
    {
        $instance = ContentQualityIssueIncompleteThead::class;

        // Test empty content
        $this->assertEquals(0, $instance::getIncompleteTheadFromContent(''));

        // Test content without tables
        $this->assertEquals(0, $instance::getIncompleteTheadFromContent('<p>No tables here.</p>'));

        // Test content with a table without thead
        $content = '<table><tr><td>Data</td></tr></table>';
        $this->assertEquals(0, $instance::getIncompleteTheadFromContent($content));

        // Test content with a table with an empty thead
        $content = '<table><thead><tr><th></th></tr></thead><tr><td>Data</td></tr></table>';
        $this->assertEquals(1, $instance::getIncompleteTheadFromContent($content));

        // Test content with a table with a non-empty thead
        $content = '<table><thead><tr><th>Header</th></tr></thead><tr><td>Data</td></tr></table>';
        $this->assertEquals(0, $instance::getIncompleteTheadFromContent($content));

        // Test content without thead, but an empty td cell on the first row
        $content = '<table><tr><td></td><td>Data</td></tr></table>';
        $this->assertEquals(1, $instance::getIncompleteTheadFromContent($content));
        
        // Test content without thead, but an empty th cell on the first row
        $content = '<table><tr><th></th><td>Data</td></tr></table>';
        $this->assertEquals(1, $instance::getIncompleteTheadFromContent($content));

        // Test 2 tables, one with an incomplete first row
        $content = '<table><tr><td>Data</td></tr></table><table><tr><td></td></tr></table>';
        $this->assertEquals(1, $instance::getIncompleteTheadFromContent($content));

        // Test 2 tables, both with incomplete first rows
        $content = '<table><tr><td></td></tr></table><table><tr><td></td></tr></table>';
        $this->assertEquals(2, $instance::getIncompleteTheadFromContent($content));
    }

    public function testIsAllWhitespaceChars(): void
    {
        $instance = ContentQualityIssueIncompleteThead::class;

        $this->assertEquals(false, $instance::isAllWhitespaceChars('a')); // Non-whitespace character
        $this->assertEquals(false, $instance::isAllWhitespaceChars('a ')); // Non-whitespace character followed by whitespace
        $this->assertEquals(false, $instance::isAllWhitespaceChars(' a')); // Whitespace followed by non-whitespace character
        
        $this->assertEquals(true, $instance::isAllWhitespaceChars(''));
        $this->assertEquals(true, $instance::isAllWhitespaceChars(' '));
        $this->assertEquals(true, $instance::isAllWhitespaceChars(' ')); // Non-breaking space
        $this->assertEquals(true, $instance::isAllWhitespaceChars('  ')); // 2x non-breaking space
        $this->assertEquals(true, $instance::isAllWhitespaceChars('   ')); // Mixture of non-breaking and regular spaces
    }
}
