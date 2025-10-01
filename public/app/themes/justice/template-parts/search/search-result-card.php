<?php

?>
<article class="search-result-card">
    <hgroup class="search-result-card__title-with-date">
        <h1 class="search-result-card__title">
            <?php if ($args['is_document']) :
                get_template_part('template-parts/common/file-download', null, [
                    'filename' => $args['title'],
                    'language' => $args['language'] ?? null,
                    'format' => $args['format'] ?? null,
                    'url' => $args['url'],
                    'filesize' => $args['filesize'] ?? null,
                ]);
            else :
                get_template_part('template-parts/common/link', null, [
                    'label' => $args['title'],
                    'url' => $args['url'],
                ]);
            endif ?>
            <span class="search-result-card__date caption"><?= esc_html($args['date']) ?></span>
        </h1>
    </hgroup>
    <p class="search-result-card__description">
        <?= wp_kses($args['description'], ['p' => [], 'span' => ['class'], 'strong' => []]) ?>
    </p>
    <p class="search-result-card__url">
        <?= esc_html($args['url']) ?>
    </p>
</article>
