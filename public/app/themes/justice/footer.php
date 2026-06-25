<?php

defined('ABSPATH') || exit;

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
                        'url' => home_url('/website-queries'),
                        'label' => 'Website queries',
                    ],
                ]
            ]); ?>
        </div>
        <?php wp_footer() ?>
    </body>
</html>
