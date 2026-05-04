<?php ob_start(); ?>
<h1><?= e(t('model.new')) ?></h1>
<form method="post">
  <label><?= e(t('model.name')) ?>
    <input type="text" name="name" required placeholder="<?= e(t('model.name_placeholder')) ?>">
  </label>
  <button type="submit"><?= e(t('common.create')) ?></button>
  <a href="?p=model" role="button" class="secondary outline"><?= e(t('common.cancel')) ?></a>
</form>
<?php
$content = ob_get_clean();
$title = t('model.new');
require __DIR__ . '/layout.php';
