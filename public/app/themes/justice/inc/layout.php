<?php

/**
 * A class to manage page layout.
 * The following body classes are for legacy css only: inner-lcr, inner-c, inner-cr
 */

namespace MOJ\Justice;

use MOJ\Justice\PostMeta;

class Layout
{
    public function __construct()
    {
        $this->addHooks();
    }

    protected function addHooks()
    {
        add_filter('body_class', [$this, 'bodyClass']);
    }

    public function bodyClass($classes)
    {
        $post_id = \get_the_ID();

        if (!isset($post_id) || !is_page()) {
            return $classes;
        }

        $post_meta = new PostMeta($post_id);
        
        $class_name = 'inner-';

        if ($post_meta->sideHasPanels('left')) {
            $class_name .= 'l';
        }

        $class_name .= 'c';

        if ($post_meta->sideHasPanels('right')) {
            $class_name .= 'r';
        }

        $classes[] = $class_name;

        return $classes;
    }
}
