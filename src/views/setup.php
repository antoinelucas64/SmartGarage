<?php ob_start(); ?>
<article style="max-width:480px; margin: 2rem auto;">
  <hgroup>
    <h1><?= e(t('auth.setup_title')) ?></h1>
    <p><?= e(t('auth.setup_intro')) ?></p>
  </hgroup>

  <?php if ($errors): ?>
    <div role="alert" style="color: #dc3545;">
      <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <label><?= e(t('settings.language')) ?>
      <select name="language">
        <?php foreach (I18n::available() as $code => $label): ?>
          <option value="<?= e($code) ?>" <?= $lang === $code ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label><?= e(t('auth.username')) ?>
      <input type="text" name="username" required autocomplete="username"
             value="<?= e($_POST['username'] ?? '') ?>">
    </label>

    <label><?= e(t('auth.password')) ?>
      <input type="password" name="password" required minlength="8" autocomplete="new-password">
      <small><?= e(t('auth.password_min')) ?></small>
    </label>

    <label><?= e(t('auth.password_confirm')) ?>
      <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password">
    </label>

    <button type="submit"><?= e(t('auth.create_account')) ?></button>
  </form>
</article>
<?php
$content = ob_get_clean();
$title = t('auth.setup_title');
$hideNav = true;
require __DIR__ . '/layout.php';
