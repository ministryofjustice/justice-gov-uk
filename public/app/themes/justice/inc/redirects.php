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
}
