<?php

namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\ContentQualityIssueSpelling;
use WP_Mock;

final class ContentQualityIssueSpellingTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;


    public function setUp(): void
    {
        parent::setUp();

        WP_Mock::setUp();

        // If no function wp_kses
        if (!function_exists('wp_kses')) {
            require_once dirname(__DIR__, 2) . '/public/wp/wp-includes/kses.php';
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();

        WP_Mock::tearDown();
    }

    public function testGetSpellingIssuesFromContent(): void
    {

        WP_Mock::userFunction('wp_allowed_protocols')
            ->with()
            ->andReturn(['http', 'https']);

        // Use a dictionary that's available in Alpine (local) and Ubuntu (GitHub PHP Test workflow).
        $dictionary_ids = ['en_GB'];

        // Create an instance of ContentQualityIssueSpelling with the dictionary IDs, and no dictionary file.
        $instance = new ContentQualityIssueSpelling($dictionary_ids, false);

        // Test empty content
        $this->assertSame([], $instance->getSpellingIssuesFromContent('', []));

        // Test content with no spelling issues
        $this->assertSame([], $instance->getSpellingIssuesFromContent('This is a test.', []));

        // Test content with one spelling issue
        $this->assertSame(['documen'], $instance->getSpellingIssuesFromContent('This is a <a>documen</a>t with a misspelled word.', []));

        // Test an american spelling
        $this->assertSame(['color'], $instance->getSpellingIssuesFromContent('color', []));

        // Test html is ignored
        $this->assertSame([], $instance->getSpellingIssuesFromContent('<img href="http://example.com" />', []));

        // Test wp typo in html comment
        $this->assertSame(['wp'], $instance->getSpellingIssuesFromContent('wp', []));

        // Test wp typo in html comment
        $this->assertSame([], $instance->getSpellingIssuesFromContent('<!-- wp:paragraph -->', []));

        // // Test content with hashtag
        $this->assertSame(['FunnyMemes'], $instance->getSpellingIssuesFromContent('FunnyMemes', []));
        $this->assertSame([], $instance->getSpellingIssuesFromContent('#FunnyMemes', []));

        // Test a place name
        $this->assertSame([], $instance->getSpellingIssuesFromContent('Birmingham', []));

        // Test single quote punctuation
        $this->assertSame([], $instance->getSpellingIssuesFromContent("It's a test.", []));

        // Test quoted words
        $this->assertSame([], $instance->getSpellingIssuesFromContent("test 'solicitor test", []));
        $this->assertSame([], $instance->getSpellingIssuesFromContent("test 'solicitor' test", []));
        $this->assertSame([], $instance->getSpellingIssuesFromContent("test solicitor' test", []));

        // Test URLs
        $this->assertSame([], $instance->getSpellingIssuesFromContent('example.com/wp-content/london', []));

        // Test words with brackets, these must be passed as allowed words, not in the dictionary.
        $this->assertSame([], $instance->getSpellingIssuesFromContent('test child(ren) test', ['child(ren)']));

        // Test content with the dictionary file
        $dictionary_file = dirname(__DIR__) . '/Unit/fixtures/content-quality-spellings.dic';
        $instance = new ContentQualityIssueSpelling($dictionary_ids, $dictionary_file);
        $this->assertSame([], $instance->getSpellingIssuesFromContent('This is a test with notaword and documen and color.', [], $dictionary_file));
    }

    public function testAllowedSpellingSanitization(): void
    {
        $instance = new ContentQualityIssueSpelling(['en_GB'], false);

        // Test empty input
        $this->assertSame('', $instance->allowedSpellingSanitization(''));

        // Test single word input
        $this->assertSame('test', $instance->allowedSpellingSanitization('test'));

        // Test multiple words input
        $input = "word1\nword2\nword3";
        $expected = "word1\nword2\nword3";
        $this->assertSame($expected, $instance->allowedSpellingSanitization($input));

        // Test input with extra spaces
        $inputWithSpaces = "  word1  \n  word2  \n  word3  ";
        $this->assertSame($expected, $instance->allowedSpellingSanitization($inputWithSpaces));

        // Test with 2 words on the same line
        $inputWithTwoWords = "word1 word2";
        $expectedWithTwoWords = "word1\nword2";
        $this->assertSame($expectedWithTwoWords, $instance->allowedSpellingSanitization($inputWithTwoWords));
    }
}
