<?php

/*
 *  A selection input element for use in forms
 *
 * Available variables:
 * - title: string A label describing the element
 * - direction: 'horizontal'|'vertical' Should the elements be vertical or inline
 * - disabled: boolean True if the input should be disabled
 * - error: boolean True if there's an error state
 * - error_text: string The text to be displayed if there's an error
 * - group: string The name of the input group
 * - options: array A list of options
 *     - label: string The label to be displayed
 *     - value: string The option value, used in form submission
 *     - checked: boolean True if the option should be checked by default
 * - hint: string A hint to add extra context to the field

 * Example usage:
 *   get_template_part('template-parts/common/selection-input', null, [
 *     'title' => 'Section',
 *     'direction' => 'vertical',
 *     'disabled' => false,
 *     'error' => false,
 *     'error_text' => '',
 *     'group' => 'section',
 *     'options' => [
 *       [
 *         'label' => 'Courts',
 *         'value' => 'courts',
 *         'checked' => true,
 *       ],
 *       [
 *         'label' => 'Publications',
 *         'value' => 'publications',
 *         'checked' => true,
 *       ],
 *     ],
 *     'hint' => 'Select one or more sections',
 *   ]);
 */

defined('ABSPATH') || exit;

if (empty($args['group'])) {
    return;
}

$defaults = [
    'title' => null,
    'direction' => null,
    'type' => null,
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

        <?php if ($args['error']) : ?>
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