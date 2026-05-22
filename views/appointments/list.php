<?php ob_start(); ?>
<h1>Список записей на просмотр</h1>

<form method="get" class="search-form">
    <input type="hidden" name="entity" value="appointment">
    <input type="hidden" name="action" value="list">
    <label>С: <input type="date" name="date_from" value="<?= h($filters['date_from']) ?>"></label>
    <label>По: <input type="date" name="date_to" value="<?= h($filters['date_to']) ?>"></label>
    <select name="status">
        <option value="">Все статусы</option>
        <option value="ожидает" <?= $filters['status']=='ожидает'?'selected':'' ?>>Ожидает</option>
        <option value="подтверждена" <?= $filters['status']=='подтверждена'?'selected':'' ?>>Подтверждена</option>
        <option value="завершена" <?= $filters['status']=='завершена'?'selected':'' ?>>Завершена</option>
        <option value="отменена" <?= $filters['status']=='отменена'?'selected':'' ?>>Отменена</option>
    </select>
    <select name="specialist_id">
        <option value="">Все специалисты</option>
        <?php foreach ($specialists as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $filters['specialist_id']==$s['id']?'selected':'' ?>><?= h($s['first_name'].' '.$s['last_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="client_search" value="<?= h($filters['client_search']) ?>" placeholder="Фамилия или телефон клиента">
    <button type="submit">Фильтровать</button>
    <a href="?entity=appointment&action=list" class="btn btn-sm btn-secondary">Сбросить</a>
</form>

<div class="btn-group" style="margin-bottom:15px;">
    <a href="?entity=appointment&action=create" class="btn btn-primary">Новая запись</a>
    <a href="?entity=appointment&action=reports" class="btn btn-secondary">Отчёты</a>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Дата/Время</th>
            <th>Клиент</th>
            <th>Объект</th>
            <th>Специалист</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($appointments as $a): ?>
        <tr data-id="<?= $a['id'] ?>">
            <td data-label="ID"><?= h($a['id']) ?></td>
           <td data-label="Дата"><?= h($a['date_time']) ?></td>
            <td data-label="Клиент"><?= h($a['client_fn'].' '.$a['client_ln']) ?><br><small><?= h($a['client_phone']) ?></small></td>
            <td data-label="Объект"><?= h($a['object_address']) ?></td>
            <td data-label="Специалист"><?= h($a['spec_fn'].' '.$a['spec_ln']) ?></td>
            <td data-label="Статус">
                <span class="status-badge status-<?= h($a['status']) ?>"><?= h($a['status']) ?></span>
            </td>
            <td data-label="Действия">
                <div class="btn-group">
                    <a href="?entity=appointment&action=view&id=<?= $a['id'] ?>" class="btn btn-sm btn-secondary">Детали</a>
                    <?php if ($a['status'] == 'ожидает'): ?>
                        <button class="btn btn-sm btn-primary ajax-status" data-id="<?= $a['id'] ?>" data-status="подтверждена">Подтвердить</button>
                        <button class="btn btn-sm btn-danger ajax-status" data-id="<?= $a['id'] ?>" data-status="отменена">Отменить</button>
                    <?php elseif ($a['status'] == 'подтверждена'): ?>
                        <button class="btn btn-sm btn-success ajax-status" data-id="<?= $a['id'] ?>" data-status="завершена">Завершить</button>
                        <button class="btn btn-sm btn-danger ajax-status" data-id="<?= $a['id'] ?>" data-status="отменена">Отменить</button>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $pagination ?>

<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    color: #fff;
    font-weight: bold;
    display: inline-block;
}
.status-ожидает { background: #f0ad4e; }
.status-подтверждена { background: #5cb85c; }
.status-завершена { background: #6c757d; }
.status-отменена { background: #d9534f; }
</style>

<script>
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('ajax-status')) {
        if (!confirm('Изменить статус?')) return;
        const btn = e.target;
        const id = btn.dataset.id;
        const status = btn.dataset.status;
        fetch('?entity=appointment&action=changeStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
            },
            body: 'id=' + id + '&status=' + encodeURIComponent(status) + '&csrf_token=<?= csrf_token() ?>'
        }).then(response => {
            if (response.ok) {
                location.reload();
            } else {
                response.text().then(msg => alert('Ошибка: ' + msg));
            }
        });
    }
});
</script>

<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>