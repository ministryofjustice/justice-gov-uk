<?php

namespace MOJ\Justice;

defined('ABSPATH') || exit;

?>

<form method="post" action="options.php">
    <?php
    settings_fields('moj_content_quality_spelling_options');
    do_settings_sections('moj_content_quality_spelling_options');

    submit_button(); ?>
</form>