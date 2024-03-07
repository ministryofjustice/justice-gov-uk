<?php

namespace Tests\Acceptance\CustomPostType;

use Tests\Support\AcceptanceTester;

class BooksCustomPostTypeCest
{
    public function _before(AcceptanceTester $I)
    {
      // I can activate the plugin successfully.
        $I->loginAsAdmin();
      // $I->amOnPluginsPage();
      // $I->seePluginInstalled('my-custom-plugin');
      // $I->activatePlugin('my-custom-plugin');
      // $I->seePluginActivated('my-custom-plugin');
      // $I->havePostInDatabase( [ 'post_title' => 'Test post', 'post_type' => "page", "post_name" => 'test', 'post_status' => 'publish' ] );
      // I can create a new post with content.
        $I->amOnPage('/wp/wp-admin/post-new.php');
        $I->fillField('.wp-block-post-title', 'Test post');
      // $I->fillField( '.wp-block-post-content p', 'This is a test post.' );
        $I->click('Publish');
      // $I->see( 'Post published' );
    }

    public function _after(AcceptanceTester $I)
    {
      // Test cleanup.
    }

    public function seeBooksCustomPostTypePage(AcceptanceTester $I)
    {
      // I go to the Books admin page, and I should be able to see the title of the CPT.
        $I->amOnPage('/');
      // $I->see('Sign in');

      // Here other acceptance criteria can be added (see columns, create new post and add content and see it successfully created, etc.).
    }
}
