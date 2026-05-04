<?php ob_start(); ?>
<hgroup>
  <h1><?= e($car['name']) ?></h1>
  <p><?= e($car['model_name']) ?>
     <?= $car['license_plate'] ? ' · ' . e($car['license_plate']) : '' ?>
     · <?= e(sprintf(t('car.in_service_at'), fmtDate($car['service_date']))) ?></p>
</hgroup>

<div class="actions">
  <a href="?p=record&a=new&car_id=<?= $car['id'] ?>" role="button"><?= e(t('car.add_record')) ?></a>
  <a href="?p=car&a=edit&id=<?= $car['id'] ?>" role="button" class="secondary outline"><?= e(t('car.edit_car')) ?></a>
  <form method="post" action="?p=car&a=delete&id=<?= $car['id'] ?>"
        onsubmit="return confirm('<?= e(t('car.delete_warning')) ?>');" style="display:inline;">
    <button type="submit" class="secondary outline"><?= e(t('common.delete')) ?></button>
  </form>
</div>

<section style="margin-top:1.5rem;">
  <h2><?= e(t('car.km_section')) ?></h2>
  <form method="post" action="?p=car&a=update_km&id=<?= $car['id'] ?>" class="km-inline">
    <input type="number" name="current_km" min="0" required value="<?= (int)$car['current_km'] ?>">
    <input type="date" name="current_km_date" required value="<?= e($car['current_km_date']) ?>">
    <button type="submit"><?= e(t('car.update_km')) ?></button>
  </form>
  <small><?= e(sprintf(t('car.last_reading'), fmtKm((int)$car['current_km']), fmtDate($car['current_km_date']))) ?></small>
</section>

<section style="margin-top:2rem;">
  <h2><?= e(t('home.alerts')) ?></h2>
  <?php if (!$alerts): ?>
    <p><em><?= e(t('home.no_alerts')) ?></em></p>
  <?php else: ?>
    <table class="tight striped">
      <thead><tr>
        <th><?= e(t('alert.maintenance')) ?></th>
        <th><?= e(t('alert.due')) ?></th>
        <th><?= e(t('alert.status')) ?></th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($alerts as $a): ?>
        <tr class="row-<?= e($a['status']) ?>">
          <td><?= e($a['type_name']) ?></td>
          <td><?= e(formatDue($a)) ?></td>
          <td><?= statusBadge($a['status']) ?></td>
          <td><a href="?p=record&a=new&car_id=<?= $car['id'] ?>"><?= e(t('alert.record_done')) ?></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<section style="margin-top:2rem;">
  <h2><?= e(t('car.history')) ?></h2>
  <?php if (!$records): ?>
    <p><em><?= e(t('car.no_records')) ?></em></p>
  <?php else: ?>
    <table class="tight striped">
      <thead><tr>
        <th><?= e(t('common.date')) ?></th>
        <th><?= e(t('record.type')) ?></th>
        <th><?= e(t('record.km')) ?></th>
        <th><?= e(t('record.cost')) ?></th>
        <th><?= e(t('common.notes')) ?></th>
        <th><?= e(t('record.invoice')) ?></th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($records as $r): ?>
        <tr>
          <td><?= fmtDate($r['date']) ?></td>
          <td><?= e($r['type_name'] ? translateTypeName($r['type_name']) : t('common.other')) ?></td>
          <td><?= $r['km'] !== null ? fmtKm((int)$r['km']) : '—' ?></td>
          <td><?= fmtMoney($r['cost'] !== null ? (float)$r['cost'] : null) ?></td>
          <td><?= e($r['notes'] ?? '') ?></td>
          <td>
            <?php if ($r['invoice_file']): ?>
              <a href="?p=invoice&id=<?= $r['id'] ?>" target="_blank">📎</a>
            <?php endif; ?>
          </td>
          <td><a href="?p=record&a=edit&id=<?= $r['id'] ?>"><?= e(t('common.edit')) ?></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?php if ($car['notes']): ?>
<section style="margin-top:2rem;">
  <h2><?= e(t('common.notes')) ?></h2>
  <p><?= nl2br(e($car['notes'])) ?></p>
</section>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = $car['name'];
require __DIR__ . '/layout.php';
