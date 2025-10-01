<?php 

defined('ABSPATH') || exit;

?>

<div class="device-only">

    <div class="anchor-link">
        <div class="bar-left"></div>
        <a href="#top"> Top ↑ </a>
        <div class="bar-right"></div>
    </div>

    <a id="phone-nav-anchor" name="phonenav"></a>

    <ul id="phone-nav-top" class="menu-top">
        <li><a href="/">Home</a></li>
    </ul>
    
    <?php get_template_part('template-parts/nav/mobile'); ?>

</div>

<div class="h-line"></div>
<div id="footer-bar">
    <div>
    </div>
</div>
<div id="footer-wrapper">
    <div class="container-wrapper">
        <div id="footer">
            <ul class="box25p">
                <li><a href="<?php echo home_url('/help/accessibility'); ?>">Accessibility</a></li>
                <li><a href="<?php echo home_url('/privacy/cookies'); ?>">Cookies</a></li>
                <li><a href="https://www.gov.uk/government/organisations/ministry-of-justice">Contacts</a></li>
                <li><a href="<?php echo home_url('/copyright'); ?>">Copyright</a></li>
                <li><a href="<?php echo home_url('/help'); ?>">Help</a></li>
                <li><a href="<?php echo home_url('/privacy'); ?>">Privacy</a></li>
                <li><a href="<?php echo home_url('/ministry-of-justice-webchats'); ?>">Webchats</a></li>
                <li><a href="<?php echo home_url('/website-queries'); ?>">Website queries</a></li>
            </ul>
            <div class="f-line"></div>
            <h1>Citizen and business advice</h1>
            <ul id="f-govuk" class="box50p">
                <li><a class="bgimg" href="http://www.gov.uk/">GOV UK</a></li>
                <li>For citizen and business advice on justice, rights and more visit
                    <a href="http://www.gov.uk/">GOV.UK</a>
                </li>
            </ul>
            <div class="f-line"></div>
            <ul id="f-copy">
                <li>© Crown copyright</li>
            </ul>
        </div>
    </div>
</div>
<?php wp_footer() ?>
</body>
</html>
