<?php

defined('ABSPATH') || exit;

$defaults = [
    'previous_url' => null,
    'next_url' => null,
    'pages' => [],
];

$args = array_merge($defaults, $args);

$total_pages = count($args['pages']);
$current_index = null;
foreach ($args['pages'] as $i => $page) {
    if (!empty($page['current'])) {
        $current_index = $i;
        break;
    }
}

$display_pages = [];
if ($total_pages <= 10) {
    $display_pages = $args['pages'];
} else {
    // Always show first 2
    $display = [0, 1];
    // Show 3 before and after current
    if ($current_index !== null) {
        for ($i = $current_index - 3; $i <= $current_index + 3; $i++) {
            if ($i > 1 && $i < $total_pages - 2) {
                $display[] = $i;
            }
        }
    }
    // Always show last 2
    $display[] = $total_pages - 2;
    $display[] = $total_pages - 1;
    // Remove duplicates and sort
    $display = array_unique($display);
    sort($display);

    // Build display_pages with ellipses
    $last = -1;
    foreach ($display as $i) {
        if ($i < 0 || $i >= $total_pages) {
            continue;
        }
        if ($last !== -1 && $i > $last + 1) {
            $display_pages[] = [
                'title' => '…',
                'url' => '',
                'current' => false,
            ];
        }
        $display_pages[] = $args['pages'][$i];
        $last = $i;
    }
}

// Build pagination links
$pagination_links = [];
foreach ($display_pages as $page) {
    if ($page['title'] === '…') {
        $pagination_links[] = '<li class="pagination__link-wrapper"><span class="pagination__link disabled" aria-hidden="true">…</span></li>';
    } elseif ($page['current'] ?? false) {
        $pagination_links[] = '<li class="pagination__link-wrapper"><a class="pagination__link disabled" role="link" aria-disabled="true" aria-current="page">' . esc_html($page['title']) . '</a></li>';
    } elseif (empty($page['url'])) {
        $pagination_links[] = '<li class="pagination__link-wrapper"><a class="pagination__link disabled" role="link" aria-disabled="true">' . esc_html($page['title']) . '</a></li>';
    } else {
        $pagination_links[] = '<li class="pagination__link-wrapper"><a class="pagination__link" href="' . esc_url($page['url']) . '">' . esc_html($page['title']) . '</a></li>';
    }
}
?>
<?php if ($total_pages > 1) : ?>
    <nav class="pagination" aria-label="pagination">
        <ul class="pagination__list">
            <li class="pagination__link-wrapper pagination__link-wrapper--previous">
                <?php if ($args['previous_url']) : ?>
                    <a class="pagination__link pagination__link--previous" href="<?= esc_url($args['previous_url']) ?>">
                        <span class="pagination__link-arrow" aria-hidden="true">«</span>
                        <span class="pagination__link-text">Previous</span>
                    </a>
                <?php else : ?>
                    <a class="pagination__link pagination__link--previous disabled" role="link" aria-disabled="true">
                        <span class="pagination__link-arrow" aria-hidden="true">«</span>
                        <span class="pagination__link-text">Previous</span></a>
                <?php endif; ?>
            </li>
            <li class="pagination__link-wrapper">
                <ul class="pagination__list pagination__sublist">
                    <?php echo implode("\n", $pagination_links); ?>
                </ul>
            </li>
            <li class="pagination__link-wrapper pagination__link-wrapper--next">
                <?php if ($args['next_url']) : ?>
                    <a class="pagination__link pagination__link--next" href="<?= esc_url($args['next_url']) ?>">
                        <span class="pagination__link-text">Next</span>
                        <span class="pagination__link-arrow" aria-hidden="true">»</span>
                    </a>
                <?php else : ?>
                    <a class="pagination__link pagination__link--next disabled" role="link" aria-disabled="true">
                        <span class="pagination__link-text">Next</span>
                        <span class="pagination__link-arrow" aria-hidden="true">»</span>
                    </a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
<?php endif; ?>
