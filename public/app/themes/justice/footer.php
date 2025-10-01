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
            <footer class="footer">
                <div class="footer__container">
                    <ul class="footer__links">
                        <li class="footer__link">
                            <a class="link" href="<?= home_url('/help/accessibility'); ?>">Accessibility</a>
                        </li>
                        <li class="footer__link">
                            <a class="link" href="<?= home_url('/privacy/cookies'); ?>">Cookies</a>
                        </li>
                        <li class="footer__link">
                            <a class="link" href="https://www.gov.uk/government/organisations/ministry-of-justice">Contacts</a>
                        </li>
                        <li class="footer__link">
                            <a class="link" href="<?= home_url('/copyright'); ?>">Copyright</a>
                        </li>
                        <li class="footer__link">
                            <a class="link" href="<?= home_url('/help'); ?>">Help</a>
                        </li>
                        <li class="footer__link">
                            <a class="link" href="<?= home_url('/privacy'); ?>">Privacy</a>
                        </li>
                        <li class="footer__link">
                            <a class="link" href="<?= home_url('/ministry-of-justice-webchats'); ?>">Webchats</a>
                        </li>
                        <li class="footer__link">
                            <a class="link" href="<?= home_url('/website-queries'); ?>">Website queries</a>
                        </li>
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
        </div>
        <?php wp_footer() ?>
    </body>
</html>