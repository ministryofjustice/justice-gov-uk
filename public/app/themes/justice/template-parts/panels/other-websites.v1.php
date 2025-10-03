<?php

defined('ABSPATH') || exit;

use MOJ\Justice\PostMeta;

$post_meta = new PostMeta();
$entries = $post_meta->getMeta('_panel_other_websites_entries');

if (empty($entries)) {
    return;
}

?>

<div class="grey-box">
    <div class="content">
        <h4>Other websites</h4>
        <ul>
            <?php foreach ($entries as $entry) { ?>
                <li>
                    <a href="<?php echo wp_kses($entry['url'], [], ['http','https']); ?>">
                        <?php echo wp_kses($entry['label'], []); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
