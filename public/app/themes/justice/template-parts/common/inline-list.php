<?php 

defined('ABSPATH') || exit;

/**
 * Inline list template part.
 * Pass an array of entries to this template part to render them as an inline list.
 */

if (empty($args['entries'])) {
    return;
}

?>

<ul class="inline-list">
    <?php foreach ($args['entries'] as $entry) { ?>
        <li>
            <a href="<?php echo $entry['url']; ?>"><?php echo $entry['title']; ?></a>
        </li>
    <?php } ?>
</ul>
