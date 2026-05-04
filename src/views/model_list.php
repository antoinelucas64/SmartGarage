<?php ob_start(); ?>
<h1><?= e(t('model.title')) ?></h1>
<p><?= e(t('model.intro')) ?></p>
<p><a href="?p=model&a=new" role="button"><?= e(t('model.new')) ?></a></p>

<?php if (!$models): ?>
  <p><em><?= e(t('model.no_models')) ?></em></p>
<?php else: ?>
<table class="tight striped">
  <thead><tr>
    <th><?= e(t('mtype.name')) ?></th>
    <th><?= e(t('model.cars')) ?></th>
    <th></th>
  </tr></thead>
  <tbody>
  <?php foreach ($models as $m): ?>
    <tr>
      <td><a href="?p=model&id=<?= $m['id'] ?>"><?= e($m['name']) ?></a></td>
      <td><?= (int)$m['car_count'] ?></td>
      <td>
        <?php if ($m['car_count'] == 0): ?>
        <form method="post" action="?p=model&a=delete&id=<?= $m['id'] ?>"
              onsubmit="return confirm('<?= e(t('common.confirm_delete')) ?>');" style="display:inline;">
          <button type="submit" class="secondary outline"><?= e(t('common.delete')) ?></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = t('model.title');
require __DIR__ . '/layout.php';
