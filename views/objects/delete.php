<?php ob_start(); ?>
<h1>Удаление объекта</h1>
<div class="delete-confirm">
    <p>Вы действительно хотите удалить объект по адресу <strong><?= h($object['address']) ?></strong>?</p>
    <form method="post" action="?entity=object&action=destroy&id=<?= $object['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button type="submit" class="btn btn-danger">Удалить</button>
        <a href="?entity=object&action=list" class="btn btn-secondary">Отмена</a>
    </form>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>