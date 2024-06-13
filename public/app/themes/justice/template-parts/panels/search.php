<?php

defined('ABSPATH') || exit;

?>

<div id="panel-SDsearch" class="grey-box">
    <div class="header">
        <span>Search this collection</span>
    </div>
    <div class="content">
        <form action="http://www.justice.gov.uk/courts/procedure-rules/civil/standard-directions/standard-directions-search" class="styled">
            <label for="query">Search standard directions content</label> 
            <input name="query" id="query" type="text" value="E.g. Witness Statements" accesskey="q" onfocus="this.value=''">
            <input class="go-btn" value="Search" type="submit"> 
            <input name="collection" value="MoJ-Standard-Directions" type="hidden"> 
            <input name="form" value="simple" type="hidden"> 
            <input name="profile" value="_default" type="hidden">
        </form>
    </div>
</div>
