<?php

/*
 * Permalinks template part.
 */

if (!defined('ABSPATH') ||
    empty($args['post_id']) ||
    empty($args['links'])
) {
    return;
}

?>

<div class="previous-permalinks">

    <strong>Previous Permalinks:</strong>

    <ul>
        <?php foreach ($args['links'] as $key => $link) : ?>
            <?php $link_text = $key === 0 ? $link['display_link'] : '...' . $link['post_name']; ?>
            <li>
                <a href="<?php echo esc_url($link['view_link']); ?>"><?= esc_html($link_text); ?></a>
                <!-- Small delete button that will trigger an ajax request -->
                <button
                    type="button"
                    class="previous-permalinks__delete button-link-delete button button-small hide-if-no-js"
                    aria-label="Delete permalink"
                    data-post-id="<?php echo esc_attr($args['post_id']); ?>"
                    data-nonce="<?php echo esc_attr($args['nonce']); ?>"
                    data-post-name="<?php echo esc_attr($link['post_name']); ?>"
                    data-permalink="<?php echo esc_attr($link['display_link']); ?>"
                    >
                    Delete
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <button
        type="button"
        class="previous-permalinks__edit button button-small hide-if-no-js"
        aria-label="Delete previous permalinks">
        Edit
    </button>

    <button
        type="button"
        class="previous-permalinks__close button-link hide-if-no-js"
        aria-label="Close edit previous permalinks">
        Close
    </button>

</div>