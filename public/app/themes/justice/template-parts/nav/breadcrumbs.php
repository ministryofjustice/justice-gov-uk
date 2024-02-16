<?php

if (!defined('ABSPATH')) {
    exit;
}

use MOJ\Justice\Breadcrumbs;

$moj_breadcrumbs = (new Breadcrumbs)->getTheBreadcrumbs();

if (!$moj_breadcrumbs) {
    return;
}

?>

<ul id="breadcrumb">
    <?php foreach ($moj_breadcrumbs as $breadcrumb) { ?>
        <li>
            <?php if ($breadcrumb['url']) { ?>
                <a href="<?php echo $breadcrumb['url']; ?>"><?php echo $breadcrumb['title']; ?></a>
            <?php } else { ?>
                <?php echo $breadcrumb['title']; ?>
            <?php } ?>
        </li>

        <?php if (empty($breadcrumb['last'])) { ?>
            <li class="separator">»</li>
        <?php } ?>
    <?php } ?>
</ul>