<?php

defined('ABSPATH') || exit;

if (empty($args['links'])) {
    return;
}

?>

<nav id="navigation-main" class="navigation-main" aria-label="Primary">
    <div class="navigation-main__container">
        <ul class="navigation-main__list">
            <?php foreach ($args['links'] as $link) : ?>
                <li class="navigation-main__link<?= !empty($link['active']) ? ' navigation-main__link--active' : ''; ?>">
                    <?php
                    get_template_part(
                        'template-parts/common/link',
                        null,
                        [
                            'new_tab' => $link['newTab'] ?? false,
                            'new_tab_visually_hidden' => $link['new_tab_visually_hidden'] ?? true,
                            'on_click' => $link['on_click'] ?? null,
                            'url' => $link['url'],
                            'label' => $link['label'],
                            'aria_current' => ($link['active'] ?? false) ? 'page' : null,
                        ]
                    )
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>