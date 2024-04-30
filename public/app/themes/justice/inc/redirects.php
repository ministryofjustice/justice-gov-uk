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
        add_filter('srm_restrict_to_capability', [$this, 'addRedirectToEditor']);
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
}
