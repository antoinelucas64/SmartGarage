<?php ob_start(); ?>
<h1><?= e($model['name']) ?></h1>
<p><a href="?p=model">← <?= e(t('model.title')) ?></a></p>

<section>
  <h2><?= e(t('model.recurring')) ?></h2>
  <?php if (!$maintenances): ?>
    <p><em><?= e(t('model.no_recurring')) ?></em></p>
  <?php else: ?>
    <table class="tight striped">
      <thead><tr>
        <th><?= e(t('record.type')) ?></th>
        <th><?= e(t('model.every_km')) ?></th>
        <th><?= e(t('model.every_months')) ?></th>
        <th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($maintenances as $mm): ?>
        <tr>
          <td><?= e(translateTypeName($mm['type_name'])) ?></td>
          <td><?= $mm['recurrence_km'] ? fmtKm((int)$mm['recurrence_km']) : '—' ?></td>
          <td><?= $mm['recurrence_months'] ? (int)$mm['recurrence_months'] . ' ' . t('common.months') : '—' ?></td>
          <td>
            <form method="post" action="?p=model&a=remove_maintenance&id=<?= $model['id'] ?>" style="display:inline;">
              <input type="hidden" name="mm_id" value="<?= $mm['id'] ?>">
              <button type="submit" class="secondary outline"><?= e(t('common.remove')) ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<section style="margin-top:2rem;">
  <h3><?= e(t('model.add_recurring')) ?></h3>
  <form method="post" action="?p=model&a=add_maintenance&id=<?= $model['id'] ?>">
    <label><?= e(t('record.type')) ?>
      <select name="maintenance_type_id" required>
        <?php if (!$availableTypes): ?>
          <option value=""><?= e(t('model.all_types_added')) ?></option>
        <?php else: ?>
          <option value=""><?= e(t('model.choose_type')) ?></option>
          <?php foreach ($availableTypes as $type_): ?>
            <option value="<?= $type_['id'] ?>"><?= e(translateTypeName($type_['name'])) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <small><a href="?p=maintenance_type&a=new" target="_blank"><?= e(t('model.create_new_type')) ?></a></small>
    </label>
    <div class="grid">
      <label><?= e(t('model.every_km')) ?> (<?= e(t('common.optional')) ?>)
        <input type="number" name="recurrence_km" min="0" placeholder="30000">
      </label>
      <label><?= e(t('model.every_months')) ?> (<?= e(t('common.optional')) ?>)
        <input type="number" name="recurrence_months" min="0" placeholder="24">
      </label>
    </div>
    <small><?= e(t('model.recurrence_hint')) ?></small>
    <br><br>
    <button type="submit"><?= e(t('common.add')) ?></button>
  </form>
</section>
<?php
$content = ob_get_clean();
$title = $model['name'];
require __DIR__ . '/layout.php';
