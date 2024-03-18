<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class Search
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks()
    {
        add_filter('the_excerpt', [$this, 'highlightResults']);
    }

    public function highlightResults($text)
    {
        if (is_search()) {
            $sr = get_query_var('s');
            $keys = explode(" ", $sr);
            $text = preg_replace('/(' . implode('|', $keys) . ')/iu', '<strong class="search-excerpt">' . $sr . '</strong>', $text);
        }
        return $text;
    }
}
