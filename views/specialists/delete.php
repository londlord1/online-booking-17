<?php ob_start(); ?>
<h1>Удаление специалиста</h1>
<div class="delete-confirm">
    <p>Вы действительно хотите удалить специалиста <strong><?= h($specialist['first_name'] . ' ' . $specialist['last_name']) ?></strong>?</p>
    <form method="post" action="?entity=specialist&action=destroy&id=<?= $specialist['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button type="submit" class="btn btn-danger">Удалить</button>
        <a href="?entity=specialist&action=list" class="btn btn-secondary">Отмена</a>
    </form>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>