<?php

if (!defined('ABSPATH') || WP_ENV !== 'development') {
    exit;
}

?>

<pre class='moj-panel moj-panel--debug'><?php
foreach ($args as $content) {
    var_dump($content);
}
?></pre>
