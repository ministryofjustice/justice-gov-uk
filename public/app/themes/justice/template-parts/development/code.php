<?php

defined('ABSPATH') || exit;

if (getenv('WP_ENV') !== 'development') {
    return;
}

?>

<pre class='moj-panel moj-panel--debug'><?php
foreach ($args as $content) {
    var_dump($content);
}
?></pre>
