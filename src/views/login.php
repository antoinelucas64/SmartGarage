<?php ob_start(); ?>
<article style="max-width:420px; margin: 4rem auto;">
  <hgroup>
    <h1>🚗 <?= e(t('app.name')) ?></h1>
    <p><?= e(t('auth.login_title')) ?></p>
  </hgroup>

  <?php if ($error): ?>
    <div role="alert" style="color: #dc3545;"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <label><?= e(t('auth.username')) ?>
      <input type="text" name="username" required autocomplete="username" autofocus>
    </label>
    <label><?= e(t('auth.password')) ?>
      <input type="password" name="password" required autocomplete="current-password">
    </label>
    <button type="submit"><?= e(t('auth.login')) ?></button>
  </form>

  <footer style="text-align:center; margin-top:1rem; font-size:.85rem;">
    <?php foreach (I18n::available() as $code => $label): ?>
      <a href="?p=login&lang=<?= e($code) ?>" <?= I18n::current() === $code ? 'style="font-weight:bold"' : '' ?>><?= e($label) ?></a>
      <?= $code !== array_key_last(I18n::available()) ? ' · ' : '' ?>
    <?php endforeach; ?>
  </footer>
</article>
<?php
$content = ob_get_clean();
$title = t('auth.login_title');
$hideNav = true;
require __DIR__ . '/layout.php';
