<?php

if (!defined('ABSPATH')) {
    exit;
}

use MOJ\Justice\Breadcrumbs;

$moj_breadcrumbs = Breadcrumbs::getTheBreadcrumbs();

if (!$moj_breadcrumbs) {
    return;
}

?>

<ul id="breadcrumb">
    <?php foreach ($moj_breadcrumbs as $breadcrumb) { ?>
        <li>
            <?php if ($breadcrumb['url']) { ?>
                <a href="<?php echo $breadcrumb['url']; ?>"><?php echo $breadcrumb['label']; ?></a>
            <?php } else { ?>
                <?php echo $breadcrumb['label']; ?>
            <?php } ?>
        </li>

        <?php if (empty($breadcrumb['last'])) { ?>
            <li class="separator">»</li>
        <?php } ?>
    <?php } ?>
</ul>