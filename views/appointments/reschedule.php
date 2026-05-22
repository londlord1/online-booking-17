<?php ob_start(); ?>
<h1>Перенос записи #<?= h($appointment['id']) ?></h1>
<p>Текущее время: <?= h($appointment['date_time']) ?></p>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <label>Новая дата</label>
    <input type="date" name="date" id="new_date" value="<?= h($date) ?>" min="<?= date('Y-m-d') ?>">
    <div id="reschedule-slots" style="margin:15px 0;"></div>
    <input type="hidden" name="new_datetime" id="new_datetime">
    <button type="submit" class="btn btn-primary" id="reschedule-submit" disabled>Перенести</button>
    <a href="?entity=appointment&action=view&id=<?= $appointment['id'] ?>" class="btn btn-secondary">Отмена</a>
</form>

<script>
document.getElementById('new_date').addEventListener('change', function() {
    const date = this.value;
    const sid = <?= $appointment['specialist_id'] ?>;
    const oid = <?= $appointment['object_id'] ?>;
    const container = document.getElementById('reschedule-slots');
    container.innerHTML = 'Загрузка...';
    fetch(`get_available_slots.php?specialist_id=${sid}&date=${date}&object_id=${oid}`)
        .then(r => r.json())
        .then(data => {
            if (data.error || !data.slots.length) {
                container.innerHTML = '<p>Нет свободных слотов</p>';
                return;
            }
            let html = '';
            data.slots.forEach(slot => {
                html += `<label><input type="radio" name="slot" value="${slot}" data-datetime="${date} ${slot}:00"> ${slot}</label><br>`;
            });
            container.innerHTML = html;
            document.querySelectorAll('input[name="slot"]').forEach(r => {
                r.addEventListener('change', function() {
                    document.getElementById('new_datetime').value = this.dataset.datetime;
                    document.getElementById('reschedule-submit').disabled = false;
                });
            });
        });
});
</script>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>