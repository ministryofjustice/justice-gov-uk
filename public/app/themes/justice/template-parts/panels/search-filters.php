<?php

defined('ABSPATH') || exit;

if (empty($args['fields'])) {
    return;
}

$defaults = [
    'title' => 'Filters',
    'subtitle' => 'Filter results by',
    'fields' => [],
    'hidden_inputs' => [],
    'no_query' => false,
    'is_mobile' => false,
];

$args = array_merge($defaults, $args);

$title_id = 'sidebar-block-title-' . sanitize_title($args['title']) . ($args['is_mobile'] ? '-mobile' : '');
$content_id = 'sidebar-block-content-' . sanitize_title($args['title']) . ($args['is_mobile'] ? '-mobile' : '');

?>

<section class="sidebar-block sidebar-block--search" aria-labelledby="<?= $title_id ?>" role="complementary">
    <div class="sidebar-block__heading-wrapper">
        <h2 class="sidebar-block__heading" id="<?= $title_id ?>">
            <span class="sidebar-block__heading-text">
                <?= esc_html($args['title']) ?>
            </span>
            <button class="sidebar-block__heading-button" aria-controls="<?= $content_id ?>">
                <?= esc_html($args['title']) ?>
            </button>
        </h2>
    </div>
    <div id="<?= $content_id ?>" class="sidebar-block__content">

        <form
            class="sidebar-block__search-filter<?= $args['no_query'] ? 'sidebar-block__search-filter--disabled' : '' ?>"
            <?= $args['no_query'] ? ' aria-describedby="no-query-hint"' : '' ?>
        >
            <p class="sidebar_block__search-filter-subtitle" <?= $args['no_query'] ? ' id="no-query-hint"' : '' ?>>
                <?= $args['no_query'] ? 'Please enter a search query to use filters' : $args['subtitle'] ?>
            </p>
            <div class="sidebar-block__search-filter-fields">

                <?php foreach ($args['fields'] as $field) : ?>
                    <?php if (!empty($field['options'])) : ?>
                        <div class="sidebar-block__search-filter-field">
                            <?php
                            get_template_part('template-parts/common/selection-input', null, [
                                'title' => $field['title'] ?? null,
                                'group' => $field['group'],
                                'type' => $field['type'] ?? 'radio',
                                'direction' => 'vertical',
                                'options' => $field['options'],
                                'disabled' => $args['no_query'] || ($field['disabled'] ?? false),
                                'error' => $field['error'] ?? false,
                            ]);
                            ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php foreach ($args['hidden_inputs'] as $input) : ?>
                    <input type="hidden" name="<?= esc_attr($input['name']) ?>" value="<?= esc_attr($input['value']) ?>">
                <?php endforeach; ?>

            </div>

            <div class="sidebar-block__search-filter-buttons">
                <div class="sidebar-block__search-filter-reset">
                    <?php get_template_part('template-parts/common/button', null, [
                        'variant' => 'light',
                        'type' => 'input',
                        'input_type' => 'reset',
                        'button_text' => 'Clear filters',
                        'disabled' => $args['no_query'],
                    ]); ?>
                </div>
                <div class="sidebar-block__search-filter-submit">
                    <?php get_template_part('template-parts/common/button', null, [
                        'variant' => 'primary',
                        'type' => 'input',
                        'input_type' => 'submit',
                        'button_text' => 'Apply filters',
                        'disabled' => $args['no_query'],
                    ]); ?>
                </div>
            </div>

        </form>

    </div>
</section>
