<?php

namespace MOJ\Justice;

use Roots\WPConfig\Config;

defined('ABSPATH') || exit;

/**
 * Actions and filters related to content.
 */

class Content
{

    public function __construct()
    {
        $this->addHooks();
    }

    public function addHooks(): void
    {
        add_filter('the_content', [$this, 'fixNationalArchiveLinks']);
    }

    /**
     * Filter the content to fix broken National Archives links.
     *
     * A fix to replace the dev/demo/stage URL with the www URL when pointing to national archive URLs.
     * This only runs on non-production environments.
     *
     * e.g. It replaces the broken link:
     * https://webarchive.nationalarchives.gov.uk/ukgwa/20211201113600/https://stage.justice.gov.uk/courts/procedure-rules/family/parts/part_02
     * with the working link:
     * https://webarchive.nationalarchives.gov.uk/ukgwa/20211201113600/https://www.justice.gov.uk/courts/procedure-rules/family/parts/part_02
     *
     * @param string $content
     * @return string
     */

    public function fixNationalArchiveLinks($content)
    {

        if (Config::get('WP_ENVIRONMENT_TYPE') === 'staging') {
            // Match strings that start with https://webarchive.nationalarchives.gov.uk any amount words and / then ://stage
            $pattern = '/(https:\/\/webarchive\.nationalarchives\.gov\.uk)([\w\/]*)(:\/\/stage)/';
            return preg_replace($pattern, '$1$2://www', $content);
        }

        return $content;
    }
}
