<?php ob_start(); ?>
<h1>Справочник специалистов</h1>

<form method="get" class="search-form">
    <input type="hidden" name="entity" value="specialist">
    <input type="hidden" name="action" value="list">
    <input type="text" name="search" value="<?= h($search) ?>" placeholder="Поиск по имени, фамилии, специализации">
    <button type="submit">Найти</button>
    <a href="?entity=specialist&action=list">Сбросить</a>
</form>

<div class="btn-group" style="margin-bottom:15px;">
    <a href="?entity=specialist&action=create" class="btn btn-primary">Добавить специалиста</a>
</div>

<table>
    <thead>
        <tr>
            <th><a href="?entity=specialist&action=list&sort=id&order=<?= $sort=='id' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">ID</a></th>
            <th><a href="?entity=specialist&action=list&sort=first_name&order=<?= $sort=='first_name' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">Имя</a></th>
            <th><a href="?entity=specialist&action=list&sort=last_name&order=<?= $sort=='last_name' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">Фамилия</a></th>
            <th>Телефон</th>
            <th>Email</th>
            <th><a href="?entity=specialist&action=list&sort=specialization&order=<?= $sort=='specialization' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">Специализация</a></th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($specialists as $s): ?>
        <tr>
            <td data-label="ID"><?= h($s['id']) ?></td>
            <td data-label="Имя"><?= h($s['first_name']) ?></td>
            <td data-label="Фамилия"><?= h($s['last_name']) ?></td>
            <td data-label="Телефон"><?= h($s['phone']) ?></td>
            <td data-label="Email"><?= h($s['email']) ?></td>
            <td data-label="Специализация"><?= h($s['specialization']) ?></td>
            <td data-label="Действия">
                <div class="btn-group">
                    <a href="?entity=specialist&action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-secondary">Редактировать</a>
                    <a href="?entity=specialist&action=delete&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить специалиста?')">Удалить</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $pagination ?>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>