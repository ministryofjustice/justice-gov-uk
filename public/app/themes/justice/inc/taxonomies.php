<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

class Taxonomies
{

    public function registerHooks()
    {
        add_action('init', [$this, 'registerTaxonomies']);
    }

    public function registerTaxonomies()
    {
        register_taxonomy_for_object_type('post_tag', 'page');
    }
}
