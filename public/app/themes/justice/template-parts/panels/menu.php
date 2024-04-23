<?php

defined('ABSPATH') || exit;

use MOJ\Justice\DynamicMenu;

$moj_navigation = (new DynamicMenu)->getTheNavigation();

if (!$moj_navigation) {
    return;
}

?>

<nav>
    <ul class="menu-left">

        <?php foreach ($moj_navigation as $entry) { ?>
            <li class="level<?php echo $entry['level']; ?>">
                <a
                    href="<?php echo $entry['url']; ?>"
                    <?php if (isset($entry['selected'])) {
                        echo 'class="selected"';
                    } ?>
                >
                    <?php echo $entry['title']; ?>
                </a>
            </li>
        <?php } ?>

    </ul>
</nav>
