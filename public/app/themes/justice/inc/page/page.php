<?php

/**
 * This class is responsible for the logic related to the template page.php
 *
 * The logic is located in this controller file, leaving the template file (page.php) 
 * clean and focused on passing prepared values to Timber.
 */

namespace MOJ\Justice;

defined('ABSPATH') || exit;

require_once 'constants.php';

use MOJ\Justice\NavigationSecondary;
use MOJ\Justice\PostMeta;
use MOJ\Justice\PageConstants;
use MOJ\Justice\TemplateLinks;

class PageController
{

    // Variable to hold the PostMeta instance.
    public PostMeta $post_meta;

    public TemplateLinks $links;

    /**
     * Constructor.
     * Initializes the PostMeta instance with the current post ID.
     * This allows the controller to access post metadata and side panels.
     */
    public function __construct()
    {
        $this->post_meta = new PostMeta(get_the_ID());
        $this->links = new TemplateLinks();
    }

    /**
     * Get the template to render.
     * This checks if there are any left side panels and returns the appropriate template.
     * If there are no left side panels, it uses a single sidebar template.
     * Otherwise, it uses a two sidebar template.
     *
     * @return string The name of the template to render.
     */
    public function getTemplate(): string
    {
        return $this->post_meta->sideHasPanels('left') ? 'templates/basic--two-sidebars.html.twig' : 'templates/basic--one-sidebar.html.twig';
    }


    /**
     * Get the left side panels.
     * 
     * This checks if there are any left side panels (currently only the menu).
     * If there are no left side panels or the menu panel is not enabled, it returns an empty array.
     * Otherwise, it returns an array with the menu panel and its links.
     *
     * @return array The left side panels, or an empty array if none are enabled.
     */
    public function getLeftSidePanels(): array
    {
        if (!$this->post_meta->sideHasPanels('left') || !$this->post_meta->hasPanel('menu')) {
            return [];
        }

        return [
            'menu' => [
                ...PageConstants::PANELS_LEFT['menu'],
                'links' => (new NavigationSecondary)->getCurrentPageNavigation()
            ]
        ];
    }


    /**
     * Get the right side panels.
     *
     * This checks if there are any right side panels enabled.
     * If there are no right side panels, it returns an empty array.
     * Otherwise, it constructs an array of right side panels based on the constants defined in PageConstants.
     * It populates the links for the 'related' and 'other_websites' panels with the corresponding metadata from the post.
     *
     * @return array The right side panels, or an empty array if none are enabled.
     */
    public function getRightSidePanels(): array
    {
        if (!$this->post_meta->sideHasPanels('right')) {
            return [];
        }

        $side_panels = [];

        foreach (PageConstants::PANELS_RIGHT as $panel => $variant) {
            if ($this->post_meta->hasPanel($panel)) {

                if ('related' === $panel) {
                    $variant['links'] = $this->post_meta->getMeta('_panel_related_entries');
                }

                if ('other_websites' === $panel) {
                    $variant['links'] = $this->post_meta->getMeta('_panel_other_websites_entries');
                }

                if (empty($variant['links'])) {
                    // Skip panels with no links
                    $side_panels[$panel] = $variant;
                    continue;
                }

                // Process the links for the panel
                foreach ($variant['links'] as &$link) {
                    $link = $this->links->getLinkParams(
                        $link['url'],
                        $link['label'] ?? null,
                        $link['id'] ?? null,
                        $link['target'] ?? null
                    );
                }

                $side_panels[$panel] = $variant;
            }
        }

        return $side_panels;
    }


    /**
     * Get the updated date.
     * 
     * This checks if the '_show_updated_at' meta field is set for the post.
     * If it is set, it returns the modified date formatted as specified.
     * If not, it returns null.
     * 
     * @return string|null The formatted updated date or null if not set.
     */
    public function getUpdatedAt(): string|null
    {
        return $this->post_meta->getMeta('_show_updated_at') ? $this->post_meta->getModifiedAt() : null;
    }
}
