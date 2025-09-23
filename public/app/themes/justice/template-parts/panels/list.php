<?php

defined('ABSPATH') || exit;

$is_mobile = $args['is_mobile'] ?? false;
$is_archive = $args['is_archive'] ?? false;

$title_id = 'sidebar-block-title-' . sanitize_title($args['title'] ) . ($is_mobile ? '-mobile' : '');
$content_id = 'sidebar-block-content-' . sanitize_title($args['title'] ) . ($is_mobile ? '-mobile' : '');

?>

<section class="sidebar-block sidebar-block--list" aria-labelledby="<?= $title_id ?>" role="complementary">
    <div class="sidebar-block__heading-wrapper">
        <h2 class="sidebar-block__heading" id="<?= $title_id ?>">
            <span class="sidebar-block__heading-text <?= $is_archive ? 'sidebar-block__heading-text--archive' : '' ?>"><?= esc_html($args['title']) ?></span>
            <button class="sidebar-block__heading-button" aria-controls="<?= $content_id ?>">
                <?= $args['title']; ?>
            </button>
        </h2>
    </div>
    <div id="<?= $content_id ?>" class="sidebar-block__content">

        <ul class="sidebar-block__list">
            <?php foreach ($args['links'] as $link): ?>
                <li>
                    <?php if (isset($link['format'])): ?>
                        <!-- {% include '@components/file-download/file-download.html.twig' with {
                            format: link.format,
                            link: link.link,
                            filesize: link.filesize,
                            filename: link.filename,
                            language: link.language,
                        } %} -->
                    <?php else: ?>
                        <?php get_template_part('template-parts/common/link', null, $link); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
</section>
