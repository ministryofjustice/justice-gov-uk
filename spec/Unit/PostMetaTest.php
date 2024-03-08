<?php

namespace Tests\Unit;

use Tests\Support\UnitTester;
use MOJ\Justice\PostMeta;
use WP_Mock;

final class PostMetaTest extends \Codeception\Test\Unit
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

    public function testGetShortTitle(): void
    {
        $post_id = 1;
        $post_meta = new PostMeta($post_id);
        $short_title = 'The short title';

        WP_Mock::userFunction('get_the_title', ['times' => 0]);

        WP_Mock::userFunction('get_post_meta', [
            'times' => 1,
            'args' => [$post_id, '_short_title', true],
            'return' => $short_title
        ]);

        $this->assertEquals($short_title, $post_meta->getShortTitle($post_id));
    }

    public function testGetShortTitleMissing(): void
    {
        $post_id = 1;
        $post_meta = new PostMeta($post_id);
        $full_title = 'The full long title';

        WP_Mock::userFunction('get_the_title', ['times' => 1, 'args' => [$post_id], 'return' => $full_title]);

        WP_Mock::userFunction('get_post_meta', [
            'times' => 1,
            'args' => [$post_id, '_short_title', true],
            'return' => null
        ]);

        $this->assertEquals($full_title, $post_meta->getShortTitle($post_id));
    }
}
