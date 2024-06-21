<?php

namespace MOJ\Justice;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add scheduling to the documents post type.
 */

trait DocumentSchedule
{

    public function enqueueScheduleScripts()
    {

        // Return early if we're not on a document edit page.

        $script_asset_path = get_template_directory() . "/dist/php/document.min.asset.php";
        if (!file_exists($script_asset_path)) {
            throw new \Error(
                'You need to run `npm start` or `npm run build` for the "create-block/simple-guten-fields" block first.'
            );
        }

        $script_asset = require($script_asset_path);
        wp_enqueue_script(
            'document-schedule',
            get_template_directory_uri() . '/dist/document.min.js',
            $script_asset['dependencies'],
            $script_asset['version']
        );

        wp_enqueue_style('wp-components');
    }

    public function addScheduleMetaBox(): void
    {

        // global $wpdr;
        // if (!isset($wpdr)) {
        //     return;
        // }


        add_meta_box('revision-schedule', __('Revision Schedule', 'wp-document-revisions'), array($this, 'revision_schedule_cb'), 'document', 'normal', 'default');
    }

    public function swapRevisionMetaBox(): void
    {

        global $wpdr;
        if (!isset($wpdr)) {
            return;
        }

        // Added like this: 
        remove_meta_box('revision-log', 'document', 'normal');

        add_meta_box('revision-log', __('Revision Log', 'wp-document-revisions'), array($this, 'revision_metabox'), 'document', 'normal', 'default');
    }


    /**
     * Custom excerpt metabox CB.
     *
     * @since 0.5
     */
    public function revision_schedule_cb()
    {

?>
        <label class="screen-reader-text" for="schedule"><?php esc_html_e('Revision Schedule', 'wp-document-revisions'); ?></label>
        <!-- <textarea rows="1" cols="40" name="schedule" tabindex="6" id="schedule"></textarea> -->
        <div id="schedule-wrap"></div>
        <p><?php esc_html_e('Optionally set a date when this revision will go live.', 'wp-document-revisions'); ?></p>
    <?php
    }

    /**
     * This function is a copy of the original revision_metabox function from the WP Document Revisions plugin.
     * 
     * This allows us to add an extra column to the revision log table.
     * Changes have been commented, and `$this` has been replace with `$wpdr->admin`.
     * 
     * @param WP_Post $post
     * @return void
     */

    public function revision_metabox($post)
    {
        global $wpdr;
        if (!isset($wpdr)) {
            return;
        }
        $can_edit_doc = current_user_can('edit_document', $post->ID);
        $revisions    = $wpdr->admin->get_revisions($post->ID);
        // error_log('revisions: ' . print_r($revisions, true));
        $key          = $wpdr->admin->get_feed_key();
    ?>
        <table id="document-revisions">
            <thead>
                <tr class="header">
                    <th><?php esc_html_e('Modified', 'wp-document-revisions'); ?></th>
                    <th>ID</th>
                    <th>Attachment ID</th>
                    <th><?php esc_html_e('User', 'wp-document-revisions'); ?></th>
                    <th style="width:40%"><?php esc_html_e('Summary', 'wp-document-revisions'); ?></th>
                    <th style="width:10%"><?php esc_html_e('Schedule', 'wp-document-revisions'); ?></th>
                    <?php
                    if ($can_edit_doc) {
                    ?>
                        <th><?php esc_html_e('Actions', 'wp-document-revisions'); ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php

                $i = 0;
                foreach ($revisions as $revision) {
                    ++$i;
                    if (!current_user_can('read_document', $revision->ID)) {
                        continue;
                    }
                    // preserve original file extension on revision links.
                    // this will prevent mime/ext security conflicts in IE when downloading.
                    $attach = $wpdr->admin->get_document($revision->ID);
                    if ($attach) {
                        $fn   = get_post_meta($attach->ID, '_wp_attached_file', true);
                        $fno  = pathinfo($fn, PATHINFO_EXTENSION);
                        $info = pathinfo(get_permalink($revision->ID));
                        $fn   = $info['dirname'] . '/' . $info['filename'];
                        // Only add extension if permalink doesnt contain post id as it becomes invalid.
                        if (!strpos($info['filename'], '&p=')) {
                            $fn .= '.' . $fno;
                        }
                    } else {
                        $fn = get_permalink($revision->ID);
                    }
                ?>
                    <tr>
                        <td><a href="<?php echo esc_url($fn); ?>" title="<?php echo esc_attr($revision->post_modified); ?>" class="timestamp"><?php echo esc_html(human_time_diff(strtotime($revision->post_modified_gmt), time())); ?></a></td>
                        <td><?php echo esc_html($revision->ID); ?></td>
                        <td><?php echo esc_html($attach->ID); ?></td>
                        <td><?php echo esc_html(get_the_author_meta('display_name', $revision->post_author)); ?></td>
                        <td><?php echo esc_html($revision->post_excerpt); ?></td>
                        <td>Immediately</td>
                        <?php if ($can_edit_doc && $post->ID !== $revision->ID && $i > 2) { ?>
                            <td><a href="
					<?php
                            echo esc_url(
                                wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'revision' => $revision->ID,
                                            'action'   => 'restore',
                                        ),
                                        'revision.php'
                                    ),
                                    "restore-post_$revision->ID"
                                )
                            );
                    ?>
				" class="revision"><?php esc_html_e('Restore', 'wp-document-revisions'); ?></a></td>
                        <?php } ?>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <p style="padding-top: 10px;"><a href="<?php echo esc_url(add_query_arg('key', $key, get_post_comments_feed_link($post->ID))); ?>"><?php esc_html_e('RSS Feed', 'wp-document-revisions'); ?></a></p>
<?php
    }
}
