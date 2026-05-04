<?php ob_start(); ?>
<h1><?= e(t('mtype.new')) ?></h1>
<form method="post">
  <label><?= e(t('mtype.name')) ?>
    <input type="text" name="name" required placeholder="<?= e(t('mtype.name_placeholder')) ?>">
  </label>
  <button type="submit"><?= e(t('common.create')) ?></button>
  <a href="?p=maintenance_type" role="button" class="secondary outline"><?= e(t('common.cancel')) ?></a>
</form>
<?php
$content = ob_get_clean();
$title = t('mtype.new');
require __DIR__ . '/layout.php';
