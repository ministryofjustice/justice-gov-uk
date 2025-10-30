<?php
defined('ABSPATH') || exit;

$defaults = [
    'previous_url' => null,
    'next_url' => null,
    'pages' => [],
];

if (!isset($args)) {
    $args = [];
}
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
} elseif ($current_index === $total_pages - 1) {
    // Last page is current: only first and last as links, rest as ellipses
    $display_pages[] = $args['pages'][0];
    if ($total_pages > 2) {
        $display_pages[] = [
            'title' => '…',
            'url' => '',
            'current' => false,
        ];
    }
    $display_pages[] = $args['pages'][$total_pages - 1];
} else {
    $display = [0, $total_pages - 1];
    $window = 2;
    $min_elements = 9;

    // Initial window
    for ($i = max(1, $current_index - $window); $i <= min($total_pages - 2, $current_index + $window); $i++) {
        $display[] = $i;
    }

    // Expand window if not enough elements
    while (count($display) < $min_elements && (min($display) > 1 || max($display) < $total_pages - 2)) {
        if (min($display) > 1) {
            $display[] = min($display) - 1;
        }
        if (count($display) < $min_elements && max($display) < $total_pages - 2) {
            $display[] = max($display) + 1;
        }
    }

    $display = array_unique($display);
    sort($display);

    // Build display_pages with ellipses
    $last = -1;
    foreach ($display as $i) {
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
