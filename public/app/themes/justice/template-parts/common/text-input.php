<?php

/*
 * A text input element for use in forms
 *
 * Available variables:
 * - label_hidden: boolean Visually display the label or not. The label will always be visible to screen reader users
 * - label: string A label describing the element
 * - id: string The unique id of the text-input element
 * - name: string The name of the text-input element
 * - value: string The value of the text-input element
 *
 * Example usage:
 *   get_template_part('template-parts/common/text-input', null, [
 *     'label_hidden' => true,
 *     'label' => 'Search',
 *     'id' => 'main-search',
 *     'name' => 'search',
 *     'value' => '',
 *   ]);
 */

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
