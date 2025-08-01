<?php

/*
 * Columns template part.
 */

if (!defined('ABSPATH') || !isset($args['issues'])) {
    return;
}
?>


<?php if (empty($args['issues'])) : ?>
    <span class="dashicons dashicons-yes"></span> <?php esc_html_e('No issues', 'justice'); ?>
<?php else : ?>
    <span class="dashicons dashicons-warning"></span>
    <?php echo sprintf(_n('%d issue found', '%d issues found', count($args['issues']), 'justice'), count($args['issues'])); ?>

    <ul>
        <?php foreach ($args['issues'] as $issue) : ?>
            <li>
                <?php echo esc_html($issue); ?>
            </li>
        <?php endforeach; ?>
    </ul>

<?php endif;
