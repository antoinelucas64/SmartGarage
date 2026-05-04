<?php
// src/views/layout.php
$hideNav = $hideNav ?? false;
$lang = I18n::current();
?><!doctype html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? t('app.name')) ?> · <?= e(t('app.name')) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
<style>
  :root { --pico-font-family: "Inter", system-ui, sans-serif; }
  body > header { padding: 1rem 0; border-bottom: 1px solid var(--pico-muted-border-color); margin-bottom: 2rem; }
  body > header nav a { margin-right: 1rem; }
  body > header nav .right { float:right; }
  .badge { display:inline-block; padding:.15rem .5rem; border-radius:.25rem; font-size:.8rem; font-weight:600; }
  .badge-overdue { background:#dc3545; color:#fff; }
  .badge-warn    { background:#ffc107; color:#000; }
  .badge-ok      { background:#28a745; color:#fff; }
  .grid-cars { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:1rem; }
  .car-card { padding:1rem; border:1px solid var(--pico-muted-border-color); border-radius:.5rem; }
  .car-card h3 { margin-bottom:.25rem; }
  .car-card .plate { color:var(--pico-muted-color); font-size:.9rem; }
  table.tight td, table.tight th { padding:.4rem .6rem; }
  .actions { display:flex; gap:.5rem; flex-wrap:wrap; }
  .row-overdue td { background: rgba(220,53,69,.08); }
  .row-warn td    { background: rgba(255,193,7,.08); }
  .km-inline { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
  .km-inline input, .km-inline select { width:auto; }
  footer { margin-top:3rem; padding:1rem 0; color: var(--pico-muted-color); font-size:.85rem; text-align:center; }
</style>
</head>
<body>
<?php if (!$hideNav): ?>
<header class="container">
  <nav>
    <strong>🚗 <?= e(t('app.name')) ?></strong>
    <a href="?p=home"><?= e(t('nav.home')) ?></a>
    <a href="?p=car&a=new"><?= e(t('nav.add_car')) ?></a>
    <a href="?p=model"><?= e(t('nav.models')) ?></a>
    <a href="?p=maintenance_type"><?= e(t('nav.maintenance_types')) ?></a>
    <span class="right">
      <a href="?p=settings"><?= e(t('nav.settings')) ?></a>
      <a href="?p=logout"><?= e(t('nav.logout')) ?></a>
    </span>
  </nav>
</header>
<?php endif; ?>
<main class="container">
<?= $content ?>
</main>
<footer class="container"><?= e(t('app.name')) ?> · PHP + SQLite</footer>
</body>
</html>
