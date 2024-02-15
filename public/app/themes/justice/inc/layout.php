<?php

/**
 * A class to manage page layout
 */

namespace MOJ\Justice;

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

    public function getTheColumns()
    {
        global $post;

        if (!isset($post) || !is_page()) {
            return;
        }

        $template = get_page_template_slug($post->id);

        switch ($template) {
            case 'page_right-sidebar.php':
                return ['c','r'];
            case 'page_full-width.php':
                return ['c'];
            default:
                return ['l','c','r'];
        }
    }

    public function bodyClass($classes)
    {
        $columns = $this->getTheColumns();

        if (!$columns) {
            return $classes;
        }

        $classes[] = 'inner-' . implode('', $columns);

        return $classes;
    }


    public function hasLeftSidebar()
    {
        return  in_array('l', $this->getTheColumns());
    }

    public function hasRightSidebar()
    {
        return  in_array('r', $this->getTheColumns());
    }
}
