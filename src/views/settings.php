<?php ob_start(); ?>
<h1><?= e(t('settings.title')) ?></h1>

<?php if ($flash): ?>
  <div role="alert" style="color:#28a745;"><?= e($flash) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div role="alert" style="color:#dc3545;"><?= e($error) ?></div>
<?php endif; ?>

<section>
  <h2><?= e(t('settings.language')) ?></h2>
  <form method="post" action="?p=settings" class="km-inline">
    <input type="hidden" name="action" value="language">
    <select name="language">
      <?php foreach (I18n::available() as $code => $label): ?>
        <option value="<?= e($code) ?>" <?= $user['language'] === $code ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit"><?= e(t('common.save')) ?></button>
  </form>
</section>

<section style="margin-top:2rem;">
  <h2><?= e(t('settings.change_password')) ?></h2>
  <form method="post" action="?p=settings" style="max-width:480px;">
    <input type="hidden" name="action" value="password">
    <label><?= e(t('settings.current_password')) ?>
      <input type="password" name="current_password" required autocomplete="current-password">
    </label>
    <label><?= e(t('settings.new_password')) ?>
      <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
    </label>
    <label><?= e(t('auth.password_confirm')) ?>
      <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password">
    </label>
    <button type="submit"><?= e(t('common.save')) ?></button>
  </form>
</section>
<?php
$content = ob_get_clean();
$title = t('settings.title');
require __DIR__ . '/layout.php';
