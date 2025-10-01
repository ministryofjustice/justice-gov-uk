<?php 

defined('ABSPATH') || exit;

if (empty($args['type']) || empty($args['variant']) || empty($args['button_text'])) {
    return;
}

$defaults = [
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
    <button class="button button--<?= esc_attr($args['variant']) ?>"<?= $args['disabled'] ? ' "disabled"' : ''; ?>>
        <?= esc_html($args['button_text']); ?>
    </button>
<?php endif; ?>