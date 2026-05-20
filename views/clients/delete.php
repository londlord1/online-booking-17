<?php ob_start(); ?>
<h1>Удаление клиента</h1>
<div class="delete-confirm">
    <p>Вы действительно хотите удалить клиента <strong><?= h($client['first_name'] . ' ' . $client['last_name']) ?></strong>?</p>
    <form method="post" action="?entity=client&action=destroy&id=<?= $client['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button type="submit" class="btn btn-danger">Удалить</button>
        <a href="?entity=client&action=list" class="btn btn-secondary">Отмена</a>
    </form>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>