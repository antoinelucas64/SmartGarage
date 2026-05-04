<?php ob_start(); ?>
<h1><?= e(t('mtype.title')) ?></h1>
<p><?= e(t('mtype.intro')) ?></p>
<p><a href="?p=maintenance_type&a=new" role="button"><?= e(t('mtype.new')) ?></a></p>

<table class="tight striped">
  <thead><tr>
    <th><?= e(t('mtype.name')) ?></th>
    <th><?= e(t('mtype.used_in_models')) ?></th>
    <th><?= e(t('mtype.used_in_records')) ?></th>
    <th></th>
  </tr></thead>
  <tbody>
  <?php foreach ($types as $type_): ?>
    <tr>
      <td><?= e(translateTypeName($type_['name'])) ?></td>
      <td><?= (int)$type_['used_in_models'] ?></td>
      <td><?= (int)$type_['used_in_records'] ?></td>
      <td>
        <?php if ($type_['used_in_models'] == 0): ?>
        <form method="post" action="?p=maintenance_type&a=delete&id=<?= $type_['id'] ?>"
              onsubmit="return confirm('<?= e(t('common.confirm_delete')) ?>');" style="display:inline;">
          <button type="submit" class="secondary outline"><?= e(t('common.delete')) ?></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php
$content = ob_get_clean();
$title = t('mtype.title');
require __DIR__ . '/layout.php';
