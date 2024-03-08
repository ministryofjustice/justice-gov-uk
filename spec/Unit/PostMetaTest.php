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


    /**
     * @param string|null $short_title
     * @example ["The short title"]
     * @example [null]
     */
    public function testGetShortTitle(string | null $short_title): void
    {
        $post_id = 1;
        $post_meta = new PostMeta($post_id);
        $full_title = 'The full long title';

        if($short_title)  {
            WP_Mock::userFunction('get_the_title', ['times' => 0]);
        } else {
            WP_Mock::userFunction('get_the_title', ['times' => 1, 'args' => [$post_id], 'return' => $full_title]);
        }

        WP_Mock::userFunction('get_post_meta', [
            'times' => 1,
            'args' => [$post_id, '_short_title', true],
            'return' => $short_title
        ]);

        $expected_title = $short_title ?? $full_title;

        $this->assertEquals($expected_title, $post_meta->getShortTitle($post_id));
    }

}
