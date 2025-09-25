<?php


if (empty($args['group'])) {
    return;
}

$defaults = [
    'title' => null,
    'direction' => null,
    'disabled' => false,
    'error' => false,
    'error_text' => '',
];

$args = array_merge($defaults, $args);

$hint_id = !empty($args['hint']) ? 'selection-input-hint-' . sanitize_title($args['title']) : null;

?>

<div class="selection-input<?= $args['disabled'] ? ' selection-input--disabled' : '' ?><?= $args['error'] ? ' selection-input--error' : '' ?>">
    <fieldset class="selection-input__fieldset" <?= $args['disabled'] ? 'disabled' : '' ?> <?= $hint_id ? "aria-describedby='$hint_id'" : '' ?>>

        <?php if ($args['title']) : ?>
            <legend class="selection-input__legend">
                <?= esc_html($args['title']) ?>
            </legend>
        <?php endif; ?>

        <?php if ($error) : ?>
            <p class="selection-input__error-text"><?= esc_html($args['error_text']) ?></p>
        <?php endif; ?>

        <div class="selection-input__options<?= 'vertical' === $args['direction'] ? ' selection-input__options--vertical' : '' ?>">
            <?php foreach ($args['options'] as $option) : ?>
                <?php $input_id = 'selection-' . sanitize_title($args['group']) . '-' . sanitize_title($option['value']); ?>

                <div class="selection-input__option-wrapper">
                    <input class="selection-input__option"
                        type="<?= 'checkbox' === $args['type'] ? 'checkbox' : 'radio' ?>"
                        name="<?= esc_attr($args['group']) ?>"
                        value="<?= esc_attr($option['value']) ?>"
                        id="<?= $input_id ?>"
                        <?= $option['checked'] ?? false ? 'checked' : '' ?> />

                    <label class="selection-input__label" for="<?= $input_id ?>"><?= esc_html($option['label']) ?></label>
                </div>

            <?php endforeach; ?>
        </div>

        <?php if (!empty($args['hint'])) : ?>
            <p class="selection-input__hint" id="<?= esc_attr($args['hint']) ?>"><?= esc_html($args['hint']) ?></p>
        <?php endif; ?>

    </fieldset>
</div>