<?php

namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\Templates;

use WP_Mock;

final class TemplatesTest extends \Codeception\Test\Unit
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

    public function testReplaceDuplicateDownloadDetails(): void
    {
        // Define some HTML, where the "(PDF)" text appears multiple times.
        // This simulates a scenario where the same file download details are repeated.
        $html_pre_process = '<div class="file-download">
            <i class="file-download__icon icon-pdf--sm" aria-hidden="true"></i>
            <a class="file-download__link" href="http://justice.docker/__data/assets/pdf_file/0006/177846/cpr-166-pd-update.pdf">
                <span class="file-download__prefix visually-hidden">Download</span>
                PD making document
            </a>
            <span class="file-download__details">(PDF)</span>
            <!-- /.file-download -->
        </div> (PDF)';

        // Pass the HTML to the method that processes it.
        $html_post_process = Templates::replaceDuplicateDownloadDetails($html_pre_process);

        // Define the expected HTML after processing, where the duplicate "(PDF)" text is removed.
        $expected_html = '<div class="file-download">
            <i class="file-download__icon icon-pdf--sm" aria-hidden="true"></i>
            <a class="file-download__link" href="http://justice.docker/__data/assets/pdf_file/0006/177846/cpr-166-pd-update.pdf">
                <span class="file-download__prefix visually-hidden">Download</span>
                PD making document
            </a>
            <span class="file-download__details">(PDF)</span>
        </div>';

        // Assert that the processed HTML matches the expected HTML.
        $this->assertEquals($html_post_process, $expected_html);

        // Test for multiple occurrences of the duplicated details in a single string.
        $html_post_process_2 = Templates::replaceDuplicateDownloadDetails("$html_pre_process\n\r$html_pre_process");

        // The expected HTML should still be the same, but now it appears twice in the string.
        $expected_html_2 = "$expected_html\n\r$expected_html";

        // Assert that the processed HTML matches the expected HTML for the second case.
        $this->assertEquals($html_post_process_2, $expected_html_2);
    }
}
