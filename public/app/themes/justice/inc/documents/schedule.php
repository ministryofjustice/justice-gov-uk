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

    /**
     * Save meta box content.
     * 
     * Non working - experimental
     * 
     *
     * @param int $post_id Post ID
     */
    public function handleScheduleSave($doc_id)
    {
        // Save logic goes here. Don't forget to include nonce checks!

        global $wpdb;
        global $wpdr;
        // Return early if the schedule field or global $wpdr is not set.
        if (!isset($wpdr) || empty($_POST['schedule'])) {
            return;
        }

        $content   = get_post_field('post_content', $doc_id);
        $attach_id = $wpdr->extract_document_id($content);
        $revisions    = $wpdr->admin->get_revisions($doc_id);

        $schedule = sanitize_text_field($_POST['schedule']);
        error_log('doc_id: ' . $doc_id);
        error_log('schedule: ' . $schedule);
        error_log('content: ' . $content);
        error_log('attach_id: ' . $attach_id);

        $mock_attach_id = 1234;
        $mock_content = $wpdr->format_doc_id($mock_attach_id);
        error_log('mock_content: ' . $mock_content);
        error_log('revisions: ' . print_r($revisions, true));

        // Get the last of the revisions array
        $first_revision = $revisions[sizeof($revisions) - 1];
        // error_log('first_revision: ' . print_r($first_revision, true));

        $correct_post_content = $first_revision->post_content;

        // if($revisions[1]) {

        // 	// Revisions 0 the thing that we have just saved.
        // 	// Set it's post_date & post_date_gmt to the schedule date.
        // 	$sql = $wpdb->prepare(
        // 		"UPDATE `{$wpdb->prefix}posts` SET `post_date` = %s, `post_date_gmt` = %s WHERE `id` = %d",
        // 		$schedule,
        // 		$schedule,
        // 		$revisions[1]->ID
        // 	);

        // 	error_log('$revisions[1] : ' . print_r($revisions[1], true));
        // }



        // Update the $doc_id with this new content
        $post_table = "{$wpdb->prefix}posts";
        $sql        = $wpdb->prepare(
            "UPDATE `$post_table` SET `post_content` = %s WHERE `id` = %d AND `post_parent` = 0 ",
            $correct_post_content,
            $doc_id
        );
        $res        = $wpdb->query($sql);
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery
        clean_post_cache($doc_id);

        // Get all the revisions for the document

        // Save the post_content field to something custome for a test



    }

    // Non working - experimental

    public function onSaveRevision($post_has_changed, $last_revision, $post)
    {
        // only interested if post changed.
        // if ( ! $post_has_changed ) {
        // 	return $post_has_changed;
        // }

        // verify post type.
        if (!$this->isDocument($post->ID)) {
            return $post_has_changed;
        }

        error_log('post: ' . print_r($post, true));
        error_log('last_revision: ' . print_r($last_revision, true));

        // We know:
        // previous attachment
        // new (proposed attachment)
        // Any existing scheduling metadata 

        // We can
        // Set some meta data on the post
        // Update post_content directly by wpdb

        // A scheduled task can manage posts with meta data, to update post_content with the correct attachment ID

        // misuse of filter, but can use to determine whether the revisions can be merged.
        // keep revision if title or content (document linked only) changed. Also if author changed.
        if ($post->post_title !== $last_revision->post_title || $post->post_author !== $last_revision->post_author) {
            return true;
        }
    }
}
