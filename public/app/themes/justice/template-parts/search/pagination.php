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
} else {
    $display = [0, $total_pages - 1]; // first and last
    $window = 2;
    $min_elements = 9;

    // Initial window
    for ($i = $current_index - $window; $i <= $current_index + $window; $i++) {
        if ($i > 0 && $i < $total_pages - 1) {
            $display[] = $i;
        }
    }

    // Expand window if not enough elements
    while (count($display) + 2 < $min_elements) { // +2 for possible ellipses
        if (($current_index - $window - 1) > 0) {
            $window++;
            $i = $current_index - $window;
            if ($i > 0 && $i < $total_pages - 1) $display[] = $i;
        }
        if (count($display) + 2 >= $min_elements) break;
        if (($current_index + $window + 1) < $total_pages - 1) {
            $i = $current_index + $window;
            if ($i > 0 && $i < $total_pages - 1) $display[] = $i;
        }
        $window++;
    }

    $display = array_unique($display);
    sort($display);

    // Build display_pages with ellipses
    $last = -1;
    foreach ($display as $i) {
        if ($i < 0 || $i >= $total_pages) continue;
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
