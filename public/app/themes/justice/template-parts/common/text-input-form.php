<?php

/*
 * A search bar
 *
 * Available variables:
 * - action: string The form action
 * - id: string The unique id of the form element
 * - input: object The variables for the input component
 *   - id: string The unique id of the input element
 *   - name: string The name of the input element
 *   - label: string The label for the input element
 * - hidden_inputs: array An array of hidden input elements
 *   - name: string The name of the hidden input element
 *   - value: string The value of the hidden input element
 * - button: object The variables for the button component
 *   - text: string The text for the button
 *
 * Example usage:
 *   get_template_part('template-parts/common/text-input-form', null, [
 *     'id' => 'search-bar-main',
 *     'action' => '/search',
 *     'input' => [
 *       'id' => 'searchbox-top',
 *       'name' => 's',
 *       'label' => 'Search',
 *       'label_hidden' => true,
 *     ],
 *     'hidden_inputs' => [
 *       ['name' => 'orderby', 'value' => 'relevance'],
 *     ],
 *     'button' => ['text' => 'Search']
 *   ]);
 */

defined('ABSPATH') || exit;

if (
    // Form
    empty($args['id']) ||
    empty($args['action']) ||
    // Input
    empty($args['input']['id']) ||
    empty($args['input']['name']) ||
    empty($args['input']['label']) ||
    // Button
    empty($args['button']['text'])
) {
    return;
}

$defaults = [
    'hidden_inputs' => [],
];

$args = array_merge($defaults, $args);

?>

<form id="<?= esc_attr($args['id']) ?>" class="text-input-form" action="<?= esc_attr($args['action']) ?>">
    <div class="text-input-form__wrapper">
        <div class="text-input-form__input">
            <?php get_template_part('template-parts/common/text-input', null, $args['input']); ?>
        </div>
        <div class="text-input-form__button">
            <?php get_template_part('template-parts/common/button', null, [
                'variant' => 'primary',
                'type' => 'input',
                'input_type' => 'submit',
                'button_text' => $args['button']['text'],
            ]); ?>
        </div>
        <?php foreach ($args['hidden_inputs'] as $input) : ?>
            <input type="hidden" name="<?= esc_attr($input['name']); ?>" value="<?= esc_attr($input['value']); ?>">
        <?php endforeach; ?>
    </div>
</form>
