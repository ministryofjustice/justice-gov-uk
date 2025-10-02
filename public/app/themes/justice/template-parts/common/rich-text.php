<?php

/*
 * A component to display user created html
 *
 * Available variables:
 * - content: string The HTML content to display
 *
 * Example:
 *  get_template_part('template-parts/common/rich-text', null, [
 *   'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit <a href="#">Click here</a></p>',
 *  ]);
 */

defined('ABSPATH') || exit;

?>
<div class="rich-text">
    <?= wp_kses_post($args['content']) ?>
</div>
