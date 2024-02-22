<?php

$source_url = get_post_meta($post->ID, '_source_url', true);

if (!$source_url) {
    return;
}

?>

<div class="grey-box">
    <div class="content">
        <h4 class="">Development</h4>
        <p>
            This is a development version of the website. It is not intended for public use.
        </p>
        <p>
            <a href="<?php echo $source_url; ?>">View this page on the live website</a>
        </p>
    </div>
</div>