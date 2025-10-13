<?php

defined('ABSPATH') || exit;

use Roots\WPConfig\Config;

if (Config::get('FRONTEND_VERSION') === 1) {
    require get_template_directory() . '/footer.v1.php';
    return;
}

?>
        </main>
        <div class="footer-wrapper">
            <?php get_template_part('template-parts/common/footer', null, [
                'links' => [
                    [
                        'url' => home_url('/help/accessibility'),
                        'label' => 'Accessibility',
                    ],
                    [
                        'url' => home_url('/privacy/cookies'),
                        'label' => 'Cookies',
                    ],
                    [
                        'url' => 'https://www.gov.uk/government/organisations/ministry-of-justice',
                        'label' => 'Contacts',
                    ],
                    [
                        'url' => home_url('/copyright'),
                        'label' => 'Copyright',
                    ],
                    [
                        'url' => home_url('/help'),
                        'label' => 'Help',
                    ],
                    [
                        'url' => home_url('/privacy'),
                        'label' => 'Privacy',
                    ],
                    [
                        'url' => home_url('/ministry-of-justice-webchats'),
                        'label' => 'Webchats',
                    ],
                    [
                        'url' => home_url('/website-queries'),
                        'label' => 'Website queries',
                    ],
                ]
            ]); ?>
        </div>
        <?php wp_footer() ?>
    </body>
</html>
