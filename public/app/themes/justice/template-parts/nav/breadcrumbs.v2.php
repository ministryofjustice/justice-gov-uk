<?php

defined('ABSPATH') || exit;

if (empty($args['links'])) {
    return;
}

?>

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <ul class="breadcrumbs__list">
        <?php foreach ($args['links'] as $link) : ?>
            <li class="breadcrumbs__item">
                <?php if (empty($link['last'])) : ?>
                    <a class="breadcrumbs__link" href="<?= esc_url($link['url']) ?>"><?= esc_html($link['label']) ?></a>
                <?php else : ?>
                    <a class="breadcrumbs__link disabled" role="link" aria-disabled="true"
                        aria-current="page"><?= esc_html($link['label']) ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>