<?php

namespace Tests\Acceptance\CustomPostType;

use Tests\Support\AcceptanceTester;

class SandboxCest
{
    public function _before(AcceptanceTester $I)
    {
        // I can activate the plugin successfully.
        $I->loginAsAdmin();
        $I->amOnPage('/wp/wp-admin/post-new.php');
        $I->fillField('.wp-block-post-title', 'Test post');
        $I->click('Publish');
    }

    public function _after(AcceptanceTester $I)
    {
        // Test cleanup.
    }

    public function seeExamplePage(AcceptanceTester $I)
    {
        // I go to the Books admin page, and I should be able to see the title of the CPT.
        $I->amOnPage('/');

        // Here other acceptance criteria can be added (see columns, create new post and add content and see it successfully created, etc.).
    }
}
