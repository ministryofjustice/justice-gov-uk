<?php

if (!defined('ABSPATH') || WP_ENV !== 'development') {
    exit;
}

?>

<div class="moj-panel moj-panel--info">
    <h4 class="">Development</h4>
    <p>
        This is a development version of the website. It is not intended for public use.
    </p>
    <p>
        <a href="<?php echo $args['source_url']; ?>">View this page on the live website</a>
    </p>
</div>
