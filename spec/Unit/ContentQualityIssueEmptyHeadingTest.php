<?php

namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\ContentQualityIssueEmptyHeading;
use WP_Mock;

final class ContentQualityIssueEmptyHeadingTest extends \Codeception\Test\Unit
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

    public function testGetEmptyHeadingsFromContentBasic(): void
    {
        $instance = new ContentQualityIssueEmptyHeading();

        $this->assertEquals(0, $instance::getEmptyHeadingsFromContent(''));

        $this->assertEquals(1, $instance::getEmptyHeadingsFromContent('<h1 class="wp-block-heading"></h1>'));
        $this->assertEquals(1, $instance::getEmptyHeadingsFromContent('<h2 class="wp-block-heading"></h2>'));
        $this->assertEquals(1, $instance::getEmptyHeadingsFromContent('<h3 class="wp-block-heading"></h3>'));
        $this->assertEquals(1, $instance::getEmptyHeadingsFromContent('<h4 class="wp-block-heading"></h4>'));
        $this->assertEquals(1, $instance::getEmptyHeadingsFromContent('<h5 class="wp-block-heading"></h5>'));
        $this->assertEquals(1, $instance::getEmptyHeadingsFromContent('<h6 class="wp-block-heading"></h6>'));

        $this->assertEquals(2, $instance::getEmptyHeadingsFromContent('<h2 class="wp-block-heading"></h2><h2></h2>'));
    }

    public function testGetEmptyHeadingsFromContentNestedHtml(): void
    {
        $instance = new ContentQualityIssueEmptyHeading();

        $html_with_a_tag = '
            <h2 class="wp-block-heading">
                <a id="index" name="index">Heading text</a>
            </h2>
        ';
        $this->assertEquals(0, $instance::getEmptyHeadingsFromContent($html_with_a_tag));
        
        $html_with_a_tag = '
            <h2 class="wp-block-heading">
                <a id="index" name="index"></a>
            </h2>
        ';
        $this->assertEquals(1, $instance::getEmptyHeadingsFromContent($html_with_a_tag));

        $html_with_a_tag = '
            <h2 class="wp-block-heading">
                <a id="index" name="index"></a>
            </h2>
            <h2></h2>
        ';
        $this->assertEquals(2, $instance::getEmptyHeadingsFromContent($html_with_a_tag));
    }

    public function testGetEmptyHeadingsFromContentWithComment(): void
    {
        $instance = new ContentQualityIssueEmptyHeading();

        $html_with_comment = '
        <!-- wp:heading -->
        <h2 class="wp-block-heading"><a id="index" name="index"></a></h2>
        <!-- /wp:heading -->';

        $this->assertEquals(1, $instance::getEmptyHeadingsFromContent($html_with_comment));
    }
}
