<?php
$cars = $pdo->query('
    SELECT c.*, m.name AS model_name
    FROM cars c JOIN car_models m ON m.id = c.car_model_id
    ORDER BY c.name
')->fetchAll();

$alerts = allAlerts($pdo);
$nbOverdue = count(array_filter($alerts, fn($a) => $a['status'] === 'overdue'));
$nbWarn    = count(array_filter($alerts, fn($a) => $a['status'] === 'warn'));

ob_start(); ?>
<hgroup>
  <h1><?= e(t('home.title')) ?></h1>
  <p><?= e(sprintf(t('home.summary'), count($cars), $nbOverdue, $nbWarn)) ?></p>
</hgroup>

<section>
  <h2><?= e(t('home.my_cars')) ?></h2>
  <?php if (!$cars): ?>
    <p><em><?= e(t('home.no_cars')) ?></em>
       <a href="?p=car&a=new" role="button"><?= e(t('home.add_first_car')) ?></a></p>
  <?php else: ?>
    <div class="grid-cars">
      <?php foreach ($cars as $car): ?>
        <article class="car-card">
          <h3><a href="?p=car&id=<?= $car['id'] ?>"><?= e($car['name']) ?></a></h3>
          <div class="plate"><?= e($car['model_name']) ?>
               <?= $car['license_plate'] ? ' · ' . e($car['license_plate']) : '' ?></div>
          <p style="margin-top:.5rem;">
            <strong><?= fmtKm((int)$car['current_km']) ?></strong><br>
            <small><?= fmtDate($car['current_km_date']) ?></small>
          </p>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section style="margin-top:2rem;">
  <h2><?= e(t('home.alerts')) ?></h2>
  <?php if (!$alerts): ?>
    <p><em><?= e(t('home.no_alerts')) ?></em></p>
  <?php else: ?>
    <table class="tight striped">
      <thead>
        <tr>
          <th><?= e(t('alert.car')) ?></th>
          <th><?= e(t('alert.maintenance')) ?></th>
          <th><?= e(t('alert.due')) ?></th>
          <th><?= e(t('alert.status')) ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($alerts as $a): ?>
        <tr class="row-<?= e($a['status']) ?>">
          <td><?= e($a['car_name']) ?></td>
          <td><?= e($a['type_name']) ?></td>
          <td><?= e(formatDue($a)) ?></td>
          <td><?= statusBadge($a['status']) ?></td>
          <td><a href="?p=record&a=new&car_id=<?= $a['car_id'] ?>"><?= e(t('alert.record_done')) ?></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
$title = t('home.title');
require __DIR__ . '/layout.php';
