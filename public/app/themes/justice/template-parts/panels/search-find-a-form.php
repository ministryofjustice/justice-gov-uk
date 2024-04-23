<?php

defined('ABSPATH') || exit;

?>

<div id="panel-findForm" class="grey-box" style="display: block;">
    <div class="header"><span>Find a form</span></div>
    <div class="content">
        <form class="styled" action="/search">
            <label for="rhs-find-a-form">Form name:</label>
            <input id="rhs-find-a-form" name="query" type="text" />
            <input class="go-btn" value="Search forms" type="submit" />
            <input type="hidden" value="moj-matrix-dev-forms" name="collection" />
            <input type="hidden" value="simple" name="form" />
            <input type="hidden" value="_default" name="profile" />
        </form>
    </div>
</div>
