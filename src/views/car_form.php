<?php ob_start(); ?>
<h1><?= $car ? e(sprintf(t('car.edit'), $car['name'])) : e(t('car.new')) ?></h1>

<?php if (!$models): ?>
  <article>
    <p><?= e(t('car.no_models_warning')) ?>
       <a href="?p=model&a=new"><?= e(t('car.create_model_first')) ?></a></p>
  </article>
<?php else: ?>
<form method="post">
  <label><?= e(t('car.name')) ?>
    <input type="text" name="name" required value="<?= e($car['name'] ?? '') ?>"
           placeholder="<?= e(t('car.name_placeholder')) ?>">
  </label>

  <label><?= e(t('car.model')) ?>
    <select name="car_model_id" required>
      <?php foreach ($models as $m): ?>
        <option value="<?= $m['id'] ?>" <?= ($car['car_model_id'] ?? 0) == $m['id'] ? 'selected' : '' ?>>
          <?= e($m['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label><?= e(t('car.license_plate')) ?>
    <input type="text" name="license_plate" value="<?= e($car['license_plate'] ?? '') ?>">
  </label>

  <label><?= e(t('car.service_date')) ?>
    <input type="date" name="service_date" required value="<?= e($car['service_date'] ?? '') ?>">
  </label>

  <div class="grid">
    <label><?= e(t('car.current_km')) ?>
      <input type="number" name="current_km" min="0" required value="<?= e((string)($car['current_km'] ?? 0)) ?>">
    </label>
    <label><?= e(t('car.km_date')) ?>
      <input type="date" name="current_km_date" required value="<?= e($car['current_km_date'] ?? date('Y-m-d')) ?>">
    </label>
  </div>

  <label><?= e(t('common.notes')) ?>
    <textarea name="notes" rows="3"><?= e($car['notes'] ?? '') ?></textarea>
  </label>

  <div class="actions">
    <button type="submit"><?= $car ? e(t('common.save')) : e(t('common.create')) ?></button>
    <a href="<?= $car ? '?p=car&id=' . $car['id'] : '?p=home' ?>" role="button" class="secondary outline"><?= e(t('common.cancel')) ?></a>
  </div>
</form>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = $car ? sprintf(t('car.edit'), $car['name']) : t('car.new');
require __DIR__ . '/layout.php';
