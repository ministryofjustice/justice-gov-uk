<?php

defined('ABSPATH') || exit;

use MOJ\Justice\PostMeta;

$post_meta = new PostMeta();
$entries = $post_meta->getMeta('_panel_related_entries');

if (empty($entries)) {
    return;
}

?>

<div id="panel-relatedContent-wrapper">
    <div id="panel-relatedContent" class="grey-box phone">

        <div class="header">
            <span>Related pages</span>
        </div>

        <div class="content">
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
</div>
