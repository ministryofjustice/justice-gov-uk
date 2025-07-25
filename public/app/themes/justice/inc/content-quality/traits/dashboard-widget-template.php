<?php

/*
 * Dashboard Widget template part.
 */

if (!defined('ABSPATH') ||
    !isset($args['issues']) ||
    !isset($args['without_issue_count']) ||
    !isset($args['with_issue_count'])
) {
    return;
}
?>

<p>
    This widget will report content quality issues such as anchor links without
    a destination and tables without the header section.
</p>

<p>
    <span class="dashicons dashicons-yes"></span>
    <?php printf(
        _n(
            'There is <strong>%d</strong> page without content quality issues.',
            'There are <strong>%d</strong> pages without content quality issues.',
            $args['without_issue_count'],
            'justice'
        ),
        $args['without_issue_count']
    ); ?>
</p>

<p>
    <span class="dashicons dashicons-warning"></span>
    <?php printf(
        _n(
            'There is <strong>%d</strong> page with one or more content quality issues.',
            'There are <strong>%d</strong> pages with one or more content quality issues.',
            $args['with_issue_count'],
            'justice'
        ),
        $args['with_issue_count']
    ); ?>
</p>

<?php if (0 === $args['with_issue_count']) {
    return;
} ?>

<p>Click on the issue name to view the pages with that issue.</p>

<ul>
    <?php foreach ($args['issues'] as $issue) : ?>
        <li>
            <a href="<?php echo esc_url(add_query_arg(['content-quality-issue' => $issue['name']], admin_url('edit.php?post_type=page'))); ?>">
                <?php echo esc_html($issue['description']); ?>
            </a>
            <span class="count">(<?php echo esc_html($issue['count']); ?>)</span>
        </li>
    <?php endforeach; ?>
</ul>
