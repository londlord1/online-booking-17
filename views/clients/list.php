<?php ob_start(); ?>
<h1>Справочник клиентов</h1>

<form method="get" class="search-form">
    <input type="hidden" name="entity" value="client">
    <input type="hidden" name="action" value="list">
    <input type="text" name="search" value="<?= h($search) ?>" placeholder="Поиск по имени или телефону">
    <button type="submit">Найти</button>
    <a href="?entity=client&action=list">Сбросить</a>
</form>

<div class="btn-group" style="margin-bottom:15px;">
    <a href="?entity=client&action=create" class="btn btn-primary">Добавить нового клиента</a>
</div>

<table>
    <thead>
        <tr>
            <th><a href="?entity=client&action=list&sort=id&order=<?= $sort=='id' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">ID</a></th>
            <th><a href="?entity=client&action=list&sort=first_name&order=<?= $sort=='first_name' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">Имя</a></th>
            <th><a href="?entity=client&action=list&sort=last_name&order=<?= $sort=='last_name' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">Фамилия</a></th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Дата рождения</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clients as $c): ?>
        <tr>
            <td data-label="ID"><?= h($c['id']) ?></td>
            <td data-label="Имя"><?= h($c['first_name']) ?></td>
            <td data-label="Фамилия"><?= h($c['last_name']) ?></td>
            <td data-label="Телефон"><?= h($c['phone']) ?></td>
            <td data-label="Email"><?= h($c['email']) ?></td>
            <td data-label="Дата рождения"><?= h($c['birth_date']) ?></td>
            <td data-label="Действия">
                <div class="btn-group">
                    <a href="?entity=client&action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Редактировать</a>
                    <a href="?entity=client&action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить клиента?')">Удалить</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $pagination ?>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>