<?php

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