<?php

/*
 * The footer navigation for the site
 *
 * Available variables:
 *  - links: array An array of links to be displayed
 *   - url: string The url for the link
 *   - label: string The label for the link
 *   - new_tab: boolean Will this link open in a new tab?
 *   - new_tab_visually_hidden: boolean Will the (open in a new tab) text be shown to all users or screen readers only?
 *   - active: boolean If true, will add aria-current="page" to the link
 *
 * Example usage:
 *   get_template_part('common/footer', null, [
 *     'links' => [
 *       [
 *         'url' => 'https://google.com',
 *         'label' => 'Click here',
 *         'new_tab' => true,
 *       ],
 *       [
 *         'url' => '#',
 *         'label' => 'Click here',
 *         'active' => true, // Optional, will add aria-current="page" to the link
 *       ]
 *     ]
 *   ]);
 */

defined('ABSPATH') || exit;

?>

<footer class="footer">
    <div class="footer__container">
        <ul class="footer__links">
            <?php foreach ($args['links'] as $link) : ?>
                <li class="footer__link">
                    <a class="link" href="<?= esc_url($link['url']); ?>" <?= !empty($link['active']) ? 'aria-current="page"' : ''; ?>>
                        <?= esc_html($link['label']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="footer__details">
            <p class="footer__heading">Citizen and business advice</p>
            <div class="footer__content-wrapper">
                <?php // Hide logo link from screen readers as it's repeated in the text link ?>
                <a class="footer__gov" href="https://www.gov.uk/" tabindex="-1" aria-hidden="true">
                        <div class="footer__logo"></div>
                </a>
                <p class="footer__content">
                    For citizen and business advice on justice, rights and more visit <a href="https://www.gov.uk/">GOV.UK <span class="visually-hidden">(opens in a new tab)</span></a>
                </p>
            </div>
        </div>
        <p class="footer__copyright">
            © Crown copyright
        </p>
    </div>
</footer>
