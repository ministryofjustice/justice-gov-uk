<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

trait DocumentPermalinks
{
    public $ajax_previous_slug = '';

    public $update_post_slug_field_stage = 0;

    public function addPermalinkHooks(): void
    {
        // Sanitize the document slug before it is saved.
        add_filter('pre_wp_unique_post_slug', [$this, 'sanitizeDocumentSlug'], 10, 6);

        // Hook into the sample permalink HTML generation to append previous permalinks HTML.
        add_action('get_sample_permalink_html', [$this, 'appendPreviousPermalinks'], 10, 2);

        // Avoid a conflict between a new document slug and other documents' previous slugs.
        add_filter('wp_unique_post_slug', [$this, 'handleSlugConflict'], 10, 5);

        // Handle the deletion of a previous permalink via AJAX.
        add_action('wp_ajax_delete_previous_permalink', [$this, 'deletePreviousPermalink']);

        // Handle edits via the Quick Edit interface.
        add_action('post_updated', function ($post_id, $post_after, $post_before) {
            $this->handleDocumentPermalinkUpdate($post_id, $post_before?->post_name ?? '', $post_after->post_name);
        }, 10, 3);

        // Handle redirects when a user tries to access a previous permalink.
        add_action('template_redirect', [$this, 'redirectPreviousPermalinks'], 20, 5);

        /**
         * In public/app/plugins/wp-document-revisions/includes/class-wp-document-revisions.php
         * there is no hook for when `update_post_slug_field` is called to update the permalink.
         *
         * We can detect this ourselves by setting markers on this class, and then checking
         * them in the when get_sample_permalink_html filter is called.
         */

        // Did `check_ajax_referer( 'samplepermalink', 'samplepermalinknonce' );` run?
        add_action('check_ajax_referer', function ($action) {
            if ($action === 'samplepermalink' && $this->update_post_slug_field_stage === 0) {
                $this->update_post_slug_field_stage++;
            }
        });

        add_filter('pre_wp_unique_post_slug', function (string|null $override_slug, string $slug, int $post_id, string $post_status, string $post_type) {
            if ($post_type === 'document' && $this->update_post_slug_field_stage === 1) {
                $this->ajax_previous_slug = get_post_field('post_name', $post_id);
                $this->update_post_slug_field_stage++;
            }
            return $override_slug;
        }, 10, 5);

        add_filter('get_sample_permalink_html', function (string $return, int $post_id, string|null $new_title, string|null $new_slug) {
            if ($this->isDocument($post_id) && $this->update_post_slug_field_stage === 2) {
                $this->handleDocumentPermalinkUpdate($post_id, $this->ajax_previous_slug, $new_slug);
            }
            return $return;
        }, 1, 4);
    }


    /**
     * In public/app/plugins/wp-document-revisions/includes/class-wp-document-revisions.php
     * the `update_post_slug_field` function odes not sanitize the slug before updating it.
     * Meaning, it allows a post_name/slug with spaces to be saved to the database.
     *
     * There is no hook in that function to allow us to sanitize the slug before it is saved.
     * So, let's shoehorn the `pre_wp_unique_post_slug` hook provided by `wp_unique_post_slug`
     * to sanitize the slug before it is saved.
     *
     * This workaround can be removed if the following PR is merged:
     * @see https://github.com/wp-document-revisions/wp-document-revisions/pull/369
     *
     * @param string|null $override_slug The slug to override the default slug.
     * @param string $slug The slug to be sanitized.
     * @param int $post_id The post ID.
     * @param string $post_status The post status.
     * @param string $post_type The post type.
     * @param int $post_parent The post parent ID.
     * @return string|null The sanitized slug or the original slug if no sanitization was needed.
     */
    public function sanitizeDocumentSlug(string|null $override_slug, string $slug, int $post_id, string $post_status, string $post_type, $post_parent)
    {
        // If the post type is not 'document', we don't need to sanitize the slug.
        if ($post_type !== 'document') {
            return $override_slug;
        }

        // Sanitize the slug to remove spaces and other unwanted characters.
        $sanitized_slug = sanitize_title($slug);

        // Check if sanitizing the slug changed it.
        if ($sanitized_slug === $slug) {
            // If the slug is already sanitized, return the original override slug.
            return $override_slug;
        }

        // Since returning a value for this filter stops the rest of wp_unique_post_slug from running, run it here.
        // The only difference to the original function is that we are using the sanitized slug.
        $override_slug = wp_unique_post_slug($sanitized_slug, $post_id, $post_status, $post_type, $post_parent);

        return $override_slug;
    }


    /**
     * Append previous permalinks to the sample permalink HTML.
     *
     * This function is called when the sample permalink HTML is generated
     * (this is the permalink html that is shown under the title field in the document edit screen).
     * It appends a list of previous permalinks to the HTML.
     *
     * @param string $return The current sample permalink HTML.
     * @param int $post_id The post ID of the document.
     * @return string The updated sample permalink HTML with previous permalinks appended.
     */
    public function appendPreviousPermalinks(string $return, int $post_id): string
    {
        if (!$this->isDocument($post_id)) {
            return $return;
        }

        // Get all of the previous post names, this is ordered by oldest to newest.
        $pervious_post_names = get_post_meta($post_id, '_previous_post_name', false);

        if (empty($pervious_post_names)) {
            return $return;
        }

        $template_args = [
            'post_id' => $post_id,
            'nonce' => wp_create_nonce('moj_justice_delete_slug'),
            'links' => [],
        ];

        // Get the permalink structure for the document.
        // i.e. http://justice.docker/documents/%document%.pdf
        $permalink_structure = get_permalink($post_id, true);

        // Reverse the order of the post names to show the most recent first.
        $pervious_post_names = array_reverse($pervious_post_names);

        // Map over the post names to create the links.
        $template_args['links'] = array_map(function ($post_name) use ($permalink_structure) {
            $view_link = str_replace(array("%" . $this->slug .  "%"), $post_name, $permalink_structure);

            return [
                'post_name' => $post_name,
                'view_link' => $view_link,
                'display_link' => urldecode($view_link),
            ];
        }, $pervious_post_names);

        // Use a template part to keep the code as clean as possible.
        // Since we need to get the response as an array, use output buffering.
        ob_start();
        get_template_part('inc/documents/permalinks-template', null, $template_args);
        $return .= ob_get_clean();

        return $return;
    }


    /**
     * Handle conflicts between a document slug, and other document's previous slugs.
     *
     * This function is called by the filter at the end of the `wp_unique_post_slug` function.
     * It checks if the slug is a previous slug for any other document, and if so,
     * it adds a suffix to the slug to avoid the conflict.
     *
     * @param string $slug The slug to check for conflicts.
     * @param int $post_id The post ID of the document being checked.
     * @param string $post_status The post status of the document being checked.
     * @param string $post_type The post type of the document being checked.
     * @param int $post_parent The post parent ID of the document being checked.
     * @return string The slug with a suffix added or increased if there was a conflict.
     */
    public function handleSlugConflict(string $slug, int $post_id, string $post_status, string $post_type, int $post_parent)
    {
        if (!$this->isDocument($post_id)) {
            return $slug;
        }

        global $wp_post_statuses;

        // Is the slug a previously used slug for any other document?
        $document = get_posts([
            'post_type' => $this->slug,
            'posts_per_page' => 1,
            'post_status' => array_keys($wp_post_statuses), // Check all post statuses.
            'exclude' => [$post_id], // Exclude the current post.
            'meta_query' => [
                [
                    'key' => '_previous_post_name',
                    'value' => $slug
                ]
            ],
        ]);

        if (empty($document[0]?->ID)) {
            // There is no document with this slug as a previous post name, so we can return the slug as is.
            return $slug;
        }

        // There is a different document with this slug as a previous post name.
        // Add a suffix or increase the suffix by one, to avoid a conflict with the other document.
        $revised_slug = self::addOrIncreaseSuffix($slug);

        // Now re-run it through the wp_unique_post_slug function to ensure it is unique.
        return wp_unique_post_slug($revised_slug, $post_id, $post_status, $post_type, $post_parent);
    }


    /**
     * Admin AJAX endpoint to delete a document's previous permalink.
     *
     * This function is called when the user clicks the delete button on a previous permalink.
     * It checks if the user has permission to edit documents, and if the post is a document.
     * It then checks if the previous permalink exists in the post meta,
     * and if it does, it deletes it from the post meta.
     *
     * Finally it returns the sample permalink HTML for the document,
     * which will update the permalink field on the document edit screen.
     *
     * @return void
     */
    public function deletePreviousPermalink(): void
    {
        check_ajax_referer('moj_justice_delete_slug', 'nonce');  // Check the nonce.

        // Check that the user can edit documents.
        if (!current_user_can('edit_documents')) {
            wp_die("-1");
        }

        $post_id = intval($_POST['post_id']);

        $slug_to_delete = sanitize_title($_POST['post_name']);

        if (! $this->isDocument($post_id)) {
            wp_die("-1");
        }

        global $wp_post_statuses;

        $query_args = [
            'post_type' => $this->slug,
            'posts_per_page' => 1,
            'post_status' => array_keys($wp_post_statuses), // Check all post statuses.
            'include' => [$post_id], // Exclude the current post.
        ];

        $document = get_posts([
            ...$query_args,
            'meta_query' => [
                [
                    'key' => '_previous_post_name',
                    'value' => $slug_to_delete
                ]
            ],
        ]);

        if (isset($document[0]?->ID)) {
            delete_post_meta($post_id, '_previous_post_name', $slug_to_delete);
        }

        // If no document found, return.
        if (!isset($document[0]?->ID)) {
            $document = get_posts($query_args);
        }

        wp_die(get_sample_permalink_html($post_id, $document[0]->title, $document[0]->post_name));
    }


    /**
     * Handle the permalink update for a document.
     *
     * This function runs when a permalink has been updated.
     * - it checks if the post is a document
     * - it adds the previous slug to the post meta
     * - it removes the new slug from the previous post names if it exists.
     *
     * @param int $post_id The post ID of the document.
     * @param string $previous_slug The previous slug of the document.
     * @param string $new_slug The new slug of the document.
     */
    public function handleDocumentPermalinkUpdate(int $post_id, string $previous_slug, string $new_slug): void
    {
        if (!$this->isDocument($post_id) || $previous_slug === $new_slug) {
            return;
        }

        do_action('document_permalink_updated', $post_id, $previous_slug, $new_slug);

        if (!empty($new_slug)) {
            // If the new slug is already in the previous post names, remove it.
            delete_post_meta($post_id, '_previous_post_name', $new_slug);
        }

        if (!empty($previous_slug)) {
            // Add the previous slug to the post meta.
            add_post_meta($post_id, '_previous_post_name', $previous_slug);
        }
    }


    /**
     * Redirect to the current document permalink, if the current request is for a previous permalink.
     *
     * @return void
     */
    public function redirectPreviousPermalinks(): void
    {
        if (!is_404()) {
            return;
        }

        global $wp;
        $path_parts = pathinfo($wp->request);

        if (empty($path_parts['dirname'])) {
            // If there is no directory in the request, we cannot redirect.
            return;
        }

        // Split the directory into parts, to make sure the last part is the document slug.
        $dirname_parts = explode('/', $path_parts['dirname']);

        if (empty($path_parts['extension']) ||
            empty($path_parts['filename']) ||
            $path_parts['extension'] === 'php' ||
            end($dirname_parts) !== $this->document_slug
        ) {
            return;
        }

        $document = get_posts([
            'post_type' => $this->slug,
            'posts_per_page' => 1,
            'post_status' => ['publish', 'private'],
            'meta_query' => [
                [
                    'key' => '_previous_post_name',
                    'value' => $path_parts['filename']
                ]
            ],
        ]);

        // If no document found, return.
        if (!isset($document[0]?->ID)) {
            return;
        }

        // The document is private and the user cannot read it.
        if ($document[0]->post_status === 'private' && !current_user_can('read_private_documents')) {
            return;
        }

        wp_safe_redirect(get_permalink($document[0]->ID), 301);
        exit;
    }


    /**
     * Add or increase the suffix of a slug.
     *
     * @param string $slug The slug to add or increase the suffix for.
     * @return string The slug with the suffix added or increased.
     */
    public static function addOrIncreaseSuffix(string $slug): string
    {
        // Does the slug end in a dash and a number?
        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            $current_number = (int)$matches[1];

            if ($current_number <= 1) {
                return $slug . '-2';
            }

            if (str_starts_with($matches[0], '-0')) {
                return $slug . '-2';
            }

            $next_number = $current_number + 1;

            // Replace the last dash and number with the next number.
            $slug = preg_replace('/-(\d+)$/', '-' . $next_number, $slug);
            return $slug;
        }

        // If the slug does not have a suffix, append '-2' to it.
        return $slug . '-2';
    }
}
