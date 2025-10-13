<?php

defined('ABSPATH') || exit;

?>
<div id="header-wrapper">
    <div class="container-wrapper">
        <div id="header">
            <a name="top"></a><a name="pagetop"></a>
            <ul id="links-top">

                <li class="device-only">.</li>
            </ul>
            <div id="logo">
                <a href="/" accesskey="1">Home</a><a href="#skip_nav" style="display:none;" accesskey="s">&nbsp;</a>
            </div>
            <?php
            $menu_items = [
                [
                    'title' => 'Courts',
                    'url' => 'https://www.gov.uk/government/organisations/hm-courts-and-tribunals-service',
                    'onclick' => "gtag && gtag('event', 'page_view', { page_title: 'Courts', page_location: '" . home_url('/courts') . "' });"
                ],
                [
                    'title' => 'Procedure rules',
                    'url' => home_url('/courts/procedure-rules'),
                    'active' => str_starts_with(get_permalink(), home_url('/courts/procedure-rules')),
                    'onclick' => null
                ],
                [
                    'title' => 'Offenders',
                    'url' => 'https://www.gov.uk/government/organisations/hm-prison-and-probation-service',
                    'onclick' => "gtag && gtag('event', 'page_view', { page_title: 'Offenders', page_location: '" . home_url('/offenders') . "' });"
                ]
            ];
            ?>
            <nav>             
                <ul class="menu-top">
                    <?php foreach ($menu_items as $item) : ?>
                        <li <?php echo !empty($item['active']) ? 'class="active"' : '' ?>>
                            <a href="<?php echo $item['url']; ?>" onclick="<?= $item['onclick'] ?? '' ?>">
                                <?= $item['title'] ?>
                            </a>
                            <div class="finish"></div>
                            <span></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div id="search-top">
                <form action="/">
                    <label for="searchbox-top">Search</label>
                    <input type="text" id="searchbox-top" name="s" accesskey="4" class="ui-autocomplete-input"
                           autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true">
                    <input class="go-btn" type="submit" value="Search">
                </form>
            </div>
        </div>
    </div>
</div>
<div id="section-sub-wrapper">
    <div class="container-wrapper">
        <div id="section-sub"></div>
    </div>
</div>
<div class="h-line"></div>

