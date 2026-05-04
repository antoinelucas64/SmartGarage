<?php ob_start(); ?>
<h1><?= $record ? e(t('record.edit')) : e(t('record.new')) ?></h1>
<p><a href="?p=car&id=<?= $car['id'] ?>">← <?= e($car['name']) ?></a></p>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="car_id" value="<?= $car['id'] ?>">

  <div class="grid">
    <label><?= e(t('common.date')) ?>
      <input type="date" name="date" required value="<?= e($record['date'] ?? date('Y-m-d')) ?>">
    </label>
    <label><?= e(t('record.type')) ?>
      <select name="maintenance_type_id">
        <option value=""><?= e(t('record.type_other')) ?></option>
        <?php foreach ($types as $type_): ?>
          <option value="<?= $type_['id'] ?>" <?= ($record['maintenance_type_id'] ?? 0) == $type_['id'] ? 'selected' : '' ?>>
            <?= e(translateTypeName($type_['name'])) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <small><a href="?p=maintenance_type&a=new" target="_blank"><?= e(t('record.add_type_link')) ?></a></small>
    </label>
  </div>

  <div class="grid">
    <label><?= e(t('record.km')) ?>
      <input type="number" name="km" min="0" value="<?= e((string)($record['km'] ?? $car['current_km'])) ?>">
    </label>
    <label><?= e(t('record.cost')) ?>
      <input type="text" name="cost" inputmode="decimal"
             value="<?= e($record['cost'] !== null ? str_replace('.', ',', (string)$record['cost']) : '') ?>">
    </label>
  </div>

  <label><?= e(t('common.notes')) ?>
    <textarea name="notes" rows="3"><?= e($record['notes'] ?? '') ?></textarea>
  </label>

  <label><?= e(t('record.invoice')) ?>
    <input type="file" name="invoice" accept=".pdf,.jpg,.jpeg,.png,.webp">
    <?php if (!empty($record['invoice_file'])): ?>
      <small><?= e(sprintf(t('record.current_file'), $record['invoice_file'])) ?>
             — <a href="?p=invoice&id=<?= $record['id'] ?>" target="_blank"><?= e($record['invoice_file']) ?></a></small>
    <?php endif; ?>
  </label>

  <div class="actions">
    <button type="submit"><?= $record ? e(t('common.save')) : e(t('common.create')) ?></button>
    <a href="?p=car&id=<?= $car['id'] ?>" role="button" class="secondary outline"><?= e(t('common.cancel')) ?></a>
    <?php if ($record): ?>
      <form method="post" action="?p=record&a=delete&id=<?= $record['id'] ?>"
            onsubmit="return confirm('<?= e(t('record.delete_confirm')) ?>');" style="display:inline;">
        <button type="submit" class="secondary outline"><?= e(t('common.delete')) ?></button>
      </form>
    <?php endif; ?>
  </div>
</form>
<?php
$content = ob_get_clean();
$title = $record ? t('record.edit') : t('record.new');
require __DIR__ . '/layout.php';
