<?php

if (!defined('ABSPATH')) {
    exit;
}

if(!have_posts()) {
    return;
}

?>

<ul class="sort">
    <li>Sort by:</li>

    <?php foreach ($args['search_options'] as $option): ?>

        <?php if($option['selected']) : ?>
            <li class="selected">
                <span><?= $option['label'] ?></span>
            </li>
        <?php else : ?>
            <li>
                <a href="<?= $option['url'] ?>">
                    <?= $option['label'] ?>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($option !== end($args['search_options'])): ?>
            <li class="separator">|</li>
        <?php endif; ?>

    <?php endforeach; ?>

</ul>