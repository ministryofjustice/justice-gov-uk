<?php

defined('ABSPATH') || exit;

use MOJ\Justice\DynamicMenu;

$moj_navigation = (new DynamicMenu)->getTheNavigation('mobile-nav');

if (!$moj_navigation) {
    return;
}

?>

<ul id="phone-nav-left" class="menu-left">

    <?php foreach ($moj_navigation as $entry) { ?>
        <li class="level<?php echo $entry['level']; ?>">

            <?php if ($entry['level'] === 0) { ?>
                <span>
                    <?php echo $entry['title']; ?>
                </span>

            <?php } else { ?>
                <a href="<?php echo $entry['url']; ?>" >
                    <?php echo $entry['title']; ?>
                </a>

            <?php } ?>

        </li>

    <?php } ?>

</ul>