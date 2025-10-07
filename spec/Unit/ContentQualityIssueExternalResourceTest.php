<?php

namespace Tests\Unit;

use MOJ\Justice\ContentQualityIssueExternalResource as ExternalResource;

final class ContentQualityIssueExternalResourceTest extends \Codeception\Test\Unit
{
    public function testGetExternalResourcesFromContent(): void
    {

        // Empty
        $this->assertEquals([], ExternalResource::getExternalResourcesFromContent(''));
        // Allowed
        $this->assertEquals([], ExternalResource::getExternalResourcesFromContent('<img src="//justice.docker/logo.png" />'));
        $this->assertEquals([], ExternalResource::getExternalResourcesFromContent('<img src="//justice.gov.uk/logo.png" />'));
        // Google
        $this->assertEquals(['//google.com/logo.png'], ExternalResource::getExternalResourcesFromContent('<img src="//google.com/logo.png" />'));
        $this->assertEquals(['http://google.com/logo.png'], ExternalResource::getExternalResourcesFromContent('<img src="http://google.com/logo.png" />'));
        $this->assertEquals(['https://google.com/logo.png'], ExternalResource::getExternalResourcesFromContent('<img src="https://google.com/logo.png" />'));
        // Multiple
        $this->assertEquals(['//google.com/logo.png'], ExternalResource::getExternalResourcesFromContent('<img src="//google.com/logo.png" /><img src="//justice.docker/logo.png" />'));
        // Local file
        $this->assertEquals(['file://c/xyz'], ExternalResource::getExternalResourcesFromContent('<img src="file://c/xyz" />'));
    }
}
