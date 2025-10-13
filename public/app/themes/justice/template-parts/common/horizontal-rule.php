<?php

/*
 * An HR element that can be hidden from screenreaders when used decoratively
 *
 * Available variables:
 *  - decorative: boolean (optional) If true then hide the horizontal rule from screen readers
 * - full_width: boolean (optional) Whether the horizontal rule breaks out of the container
 *
 * Example usage:
 *   get_template_part('template-parts/common/horizontal-rule', null, [
 *    'decorative' => true,
 *   'full_width' => false,
 * ]);
 */
 
defined('ABSPATH') || exit;

$defaults = [
    'decorative' => true,
    'full_width' => false,
];

$args = array_merge($defaults, $args);

?>

<div class="horizontal-rule<?= $args['full_width'] ? ' horizontal-rule--full-width' : '' ?>">
    <hr class="horizontal-rule__hr"
        aria-hidden="<?= $args['decorative'] ? 'true' : 'false' ?>">
</div>
