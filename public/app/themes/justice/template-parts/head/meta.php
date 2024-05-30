<?php

defined('ABSPATH') || exit;

?>

<meta name="DC.title" content="<?= wp_get_document_title(); ?>">
<meta name="Organisation" content="MOJ">
<?php if (!empty($args['audience'])) { ?>
    <meta name="Audience" content="<?= $args['audience'] ?>">
<?php } ?>
<?php if (!empty($args['section'])) { ?>
    <meta name="Section" content="<?= $args['section'] ?>">
<?php } ?>
<?php if (!empty($args['type'])) { ?>
    <meta name="Content-type" content="<?= $args['type'] ?>">
    <meta name="DC.type" content="<?= $args['type'] ?>">
<?php } ?>
<meta name="DC.date.created" content="<?= get_the_date('Y-m-d') ?>">
<?php if (!empty($args['modified'])) { ?>
    <meta name="DC.date.modified" content="<?= $args['modified'] ?>">
<?php } ?>
