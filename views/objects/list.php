<?php ob_start(); ?>
<h1>Справочник объектов недвижимости</h1>

<form method="get" class="search-form">
    <input type="hidden" name="entity" value="object">
    <input type="hidden" name="action" value="list">
    <input type="text" name="search" value="<?= h($search) ?>" placeholder="Поиск по адресу, типу, описанию">
    <button type="submit">Найти</button>
    <a href="?entity=object&action=list">Сбросить</a>
</form>

<div class="btn-group" style="margin-bottom:15px;">
    <a href="?entity=object&action=create" class="btn btn-primary">Добавить объект</a>
</div>

<table>
    <thead>
        <tr>
            <th><a href="?entity=object&action=list&sort=id&order=<?= $sort=='id' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">ID</a></th>
            <th><a href="?entity=object&action=list&sort=address&order=<?= $sort=='address' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">Адрес</a></th>
            <th><a href="?entity=object&action=list&sort=type&order=<?= $sort=='type' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">Тип</a></th>
            <th><a href="?entity=object&action=list&sort=area&order=<?= $sort=='area' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">Площадь (м²)</a></th>
            <th><a href="?entity=object&action=list&sort=price&order=<?= $sort=='price' && $order=='ASC' ? 'desc' : 'asc' ?>&search=<?= h($search) ?>">Цена (руб)</a></th>
            <th>Описание</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($objects as $obj): ?>
        <tr>
            <td data-label="ID"><?= h($obj['id']) ?></td>
            <td data-label="Адрес"><?= h($obj['address']) ?></td>
            <td data-label="Тип"><?= h($obj['type']) ?></td>
            <td data-label="Площадь"><?= h($obj['area']) ?></td>
            <td data-label="Цена"><?= h($obj['price']) ?></td>
            <td data-label="Описание"><?= h(mb_substr($obj['description'], 0, 50)) ?>…</td>
            <td data-label="Действия">
                <div class="btn-group">
                    <a href="?entity=object&action=edit&id=<?= $obj['id'] ?>" class="btn btn-sm btn-secondary">Редактировать</a>
                    <a href="?entity=object&action=delete&id=<?= $obj['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить объект?')">Удалить</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $pagination ?>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>