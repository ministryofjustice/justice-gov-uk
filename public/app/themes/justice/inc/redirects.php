<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

/**
 * Redirects
 * Actions and filters related to redirects.
 */

class Redirects
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks()
    {
        add_filter('srm_max_redirects', fn () => 10000);
        add_filter('srm_restrict_to_capability', [$this, 'addRedirectToEditor']);
        add_action('template_redirect', [$this, 'redirectToAdmin']);
        add_action('template_redirect', [$this, 'tryCleanUrlRedirect']);
    }

    /**
     * Add redirect capability to editor role.
     *
     * This is the only way to get the capability that is used to restrict access to the plugin.
     * We copy the code from the plugin, to add the capability to the editor role.
     *
     * @param string $redirect_capability
     * @return string
     */

    public function addRedirectToEditor(string $redirect_capability): string
    {

        $roles = array('editor');

        foreach ($roles as $role) {
            $role = get_role($role);

            if (empty($role) || $role->has_cap($redirect_capability)) {
                continue;
            }

            $role->add_cap($redirect_capability);
        }

        return $redirect_capability;
    }

    /**
     * Redirect frontend urls appended with /_admin to the admin page.
     *
     * @return void
     */

    public function redirectToAdmin(): void
    {
        // If not a 404 page then return.
        if (!is_404()) {
            return;
        }

        global $wp;

        $url = home_url($wp->request);
        $pattern = '/\/_admin$/';

        // If url does not end in /_admin then return.
        if (!preg_match($pattern, $url)) {
            return;
        }

        // Is user logged in?
        if (!is_user_logged_in()) {
            // Redirect to login page, with the current url as the redirect_to parameter.
            wp_safe_redirect(wp_login_url($url));
            exit;
        }

        // Remove /_admin.
        $post_url = preg_replace($pattern, '', $url);

        // Get the post id from the url.
        $post_id = url_to_postid($post_url);

        if (!$post_id) {
            return;
        }

        // Redirect to the post edit page. 302 is the default status code.
        wp_safe_redirect(get_edit_post_link($post_id, '_admin'));
        exit;
    }

    /**
     * Remove special chars from url, because some legacy urls had commas.
     * 
     * This is used in cases where the content has internal links with URLs that have commas in them.
     * To avoid updating this content, let's handle these links and redirect them to pages, if a page exists.
     *
     * @return void
     */

    public function tryCleanUrlRedirect(): void
    {
        // If not a 404 page then return.
        if (!is_404()) {
            return;
        }

        global $wp;

        // Takes path e.g. 'courts/procedure-rules/family/practice_directions/practice-direction-,with-comma'
        // Returns array of parts e.g. ['courts', ... 'practice-direction-with-comma'].
        $clean_parts = array_map(fn ($part) => sanitize_title($part), explode('/', $wp->request));

        // Get string from the array.
        $clean_path = implode('/', $clean_parts);

        // Does our cleaned path match the request?
        if ($wp->request === $clean_path) {
            return;
        }

        // Get the post id from the path.
        $post_id = url_to_postid($clean_path);

        if (!$post_id) {
            return;
        }

        // 301 redirect to the correct page.
        wp_safe_redirect(get_the_permalink($post_id), 301);
        exit;
    }
}
