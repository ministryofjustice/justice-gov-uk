<?php

defined('ABSPATH') || exit;

if (empty($args['article_id'])) {
    return;
}

?>

<div class="skip-link">
    <a class="skip-link__link" href="<?= esc_attr($args['article_id']) ?>">Skip to main content</a>
</div>
