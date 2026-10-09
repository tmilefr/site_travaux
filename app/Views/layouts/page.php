<?php
/**
 * Page complète : gabarit layouts/main + la vue de contenu choisie par le contrôleur ($content_view).
 *
 * @var string $content_view nom de la vue de contenu (ex. unique/Familys_controller/list_view)
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= $this->include($content_view) ?>
<?= $this->endSection() ?>
