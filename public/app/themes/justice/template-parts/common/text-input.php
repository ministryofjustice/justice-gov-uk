<?php 

defined('ABSPATH') || exit;

if (empty($args['id']) ||
    empty($args['name']) ||
    empty($args['label'])
) {
    return;
}

$defaults = [
    'value' => '',
    'disabled' => false,
    'error' => false,
    'label_hidden' => false,
];

$args = array_merge($defaults, $args);

?>

<div class="text-input<?= $args['disabled'] ? ' text-input--disabled' : '' ?>">
    <label class="text-input__label<?= $args['label_hidden'] ? ' visually-hidden' : '' ?>" for="<?= esc_attr($args['id']) ?>">
        <?= esc_html($args['label']) ?>
    </label>
    <input
        id="<?= esc_attr($args['id']) ?>"
        class="text-input__input<?= $args['error'] ? ' text-input__input--error' : '' ?>"
        name="<?= esc_attr($args['name']) ?>"
        type="text"
        value="<?= esc_attr($args['value']) ?>"
        <?= $args['disabled'] ? '"disabled"' : ''; ?>>
</div>