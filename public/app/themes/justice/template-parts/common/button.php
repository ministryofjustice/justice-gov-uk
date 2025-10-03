<?php

/*
 * A button or an input element with button styling
 *
 * Available variables:
 *   - variant: 'primary'|'secondary' The button type
 *   - type: 'input'|null Whether the button is an <input> or <button> element
 *   - input_type: 'button'|'submit'|null Allows us to specify the input type
 *   - button_text: string The text to be displayed
 *
 * example:
 *   get_template_part('common/button', null, [
 *       'variant' => 'primary',
 *       'type' => 'input',
 *       'input_type' => 'submit',
 *       'button_text' => 'Submit'
 *   ]);
 */

defined('ABSPATH') || exit;

if (empty($args['variant']) || empty($args['button_text'])) {
    return;
}

$defaults = [
    'type' => 'button', // Default to button type
    'input_type' => 'button', // Default to button type
    'disabled' => false,
];

$args = array_merge($defaults, $args);

?>

<?php if ($args['type'] === 'input') : ?>
    <input
        class="button button--input button--<?= esc_attr($args['variant']) ?><?= $args['disabled'] ? ' button--disabled' : ''; ?>"
        type="<?= esc_attr($args['input_type']) ?>"
        value="<?= esc_html($args['button_text']); ?>"
        <?= $args['disabled'] ? '"disabled"' : ''; ?> />
<?php else : ?>
    <button class="button button--<?= esc_attr($args['variant']) ?>" <?= $args['disabled'] ? ' "disabled"' : ''; ?>>
        <?= esc_html($args['button_text']); ?>
    </button>
<?php endif; ?>