<?php if (!empty($args['items'])) : ?>
    <?php if (0 === ($args['menu_level'])) : ?>
        <?php print('<ul class="navigation-secondary__list">'); ?>

        <li class="navigation-secondary__skip-to-article navigation-secondary__list-item navigation-secondary__list-item--level-<?= esc_attr($args['menu_level']) ?>">
            <div class="navigation-secondary__list-item-wrapper">
                <a class="navigation-secondary__link" href="<?= esc_attr($args['article_id']) ?>">Skip to main content</a>
            </div>
        </li>
    <?php else : ?>
        <?php
        // menu_level is 1 or more  */
        printf(
            '<ul id="%s" class="navigation-secondary__sublist navigation-secondary__list--level-%s %s">',
            esc_attr($args['menu_id']),
            esc_attr($args['menu_level']),
            ($args['expanded'] ?? false) ? 'navigation-secondary__sublist--open' : ''
        );
        ?>
    <?php endif; ?>

    <?php foreach ($args['items'] as $item) : ?>
        <li class="navigation-secondary__list-item navigation-secondary__list-item--level-<?= esc_attr($args['menu_level']) ?>
                <?= ($item['active'] ?? false) ? ' navigation-secondary__list-item--active' : '' ?>">

            <?php if (!empty($item['children'])) : ?>
                <div class="navigation-secondary__list-item-wrapper navigation-secondary__list-item-wrapper--has-sublist">
                    <a class="navigation-secondary__link navigation-secondary__link--has-sublist" role="link"
                        <?= ($item['active'] ?? false) ? 'aria-current="page" aria-disabled="true"' : 'href="' . esc_url($item['url']) . '"' ?>>
                        <?= esc_html($item['label']) ?>
                    </a>
                    <button class="navigation-secondary__button navigation-secondary__button--sublist"
                        aria-controls="<?= esc_attr($item['id']) ?>"
                        <?= ($item['expanded'] ?? false) ? 'aria-expanded="true" aria-label="Show less"' : 'aria-label="Show more"' ?>>
                    </button>
                </div>
                <?php
                get_template_part('template-parts/nav/navigation-secondary-inner', null, [
                    'items' => $item['children'],
                    'menu_level' => $args['menu_level'] + 1,
                    'menu_id' => $item['id'],
                    'article_id' => null, // No article ID for submenus
                    'expanded' => $item['expanded'] ?? false,
                ]);
                ?>
            <?php else : ?>
                <div class="navigation-secondary__list-item-wrapper">
                    <a class="navigation-secondary__link" role="link"
                        <?= ($item['active'] ?? false) ? 'aria-current="page" aria-disabled="true"' : 'href="' . esc_url($item['url']) . '"' ?>>
                        <?= esc_html($item['label']) ?>
                    </a>
                </div>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>

    <?php
    // Close the unordered list that is opened at the start of the macro.
    print('</ul>');
    ?>
<?php endif;
