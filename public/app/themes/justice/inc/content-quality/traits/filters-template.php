<?php

/*
 * Filters template part.
 */

if (!defined('ABSPATH') ||
    empty($args['label']) ||
    empty($args['query_key']) ||
    empty($args['values']) ||
    !isset($args['value'])
) {
    return;
}

?>

<select name="<?= $args['query_key'] ?>">
    <option value=""><?= $args['label'] ?></option>
    <?php
    foreach ($args['values'] as $label => $value) {
        printf(
            '<option value="%s"%s>%s</option>',
            $value,
            $value == $args['value'] ? ' selected="selected"' : '',
            $label
        );
    }
    ?>
</select>
