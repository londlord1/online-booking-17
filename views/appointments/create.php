<?php ob_start(); ?>
<h1>Новая запись на просмотр</h1>

<form id="booking-form" method="post" action="?entity=appointment&action=store">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <label class="required">Клиент</label>
    <select name="client_id" required class="<?= error_class($errors, 'client_id') ?>">
        <option value="">-- выберите --</option>
        <?php foreach ($clients as $c): ?>
            <option value="<?= $c['id'] ?>" <?= old('client_id')==$c['id']?'selected':'' ?>><?= h($c['last_name'].' '.$c['first_name'].' ('.$c['phone'].')') ?></option>
        <?php endforeach; ?>
    </select>
    <?= error_text($errors, 'client_id') ?>

    <label class="required">Объект недвижимости</label>
    <select name="object_id" id="object_id" required class="<?= error_class($errors, 'object_id') ?>">
        <option value="">-- выберите --</option>
        <?php foreach ($objects as $obj): ?>
            <option value="<?= $obj['id'] ?>" <?= old('object_id')==$obj['id']?'selected':'' ?>><?= h($obj['address'].' ('.$obj['type'].', '.$obj['price'].' руб.)') ?></option>
        <?php endforeach; ?>
    </select>
    <?= error_text($errors, 'object_id') ?>

    <label class="required">Специалист</label>
    <select name="specialist_id" id="specialist_id" required class="<?= error_class($errors, 'specialist_id') ?>">
        <option value="">-- сначала выберите объект --</option>
    </select>
    <?= error_text($errors, 'specialist_id') ?>

    <label class="required">Дата</label>
    <input type="date" name="appointment_date" id="appointment_date" required min="<?= date('Y-m-d') ?>" value="<?= old('appointment_date') ?>">

    <div id="slots-container" style="margin:15px 0;">
        <label>Доступное время</label>
        <div id="slots"></div>
    </div>

    <input type="hidden" name="appointment_datetime" id="appointment_datetime">

    <button type="submit" class="btn btn-primary" id="submit-btn" disabled>Создать запись</button>
    <a href="?entity=appointment&action=list" class="cancel-link">Отмена</a>
</form>

<script>
const specialistSelect = document.getElementById('specialist_id');
const objectSelect = document.getElementById('object_id');
const dateInput = document.getElementById('appointment_date');
const slotsDiv = document.getElementById('slots');
const hiddenDatetime = document.getElementById('appointment_datetime');
const submitBtn = document.getElementById('submit-btn');
let selectedSlot = null;

// Загрузка специалистов при смене объекта (здесь упрощённо загружаем всех, т.к. любой специалист может показывать любой объект)
objectSelect.addEventListener('change', function() {
    fetch('?entity=specialist&action=list&format=json')
        .then(r => r.json())
        .then(data => {
            specialistSelect.innerHTML = '<option value="">-- выберите --</option>';
            data.forEach(s => {
                specialistSelect.innerHTML += `<option value="${s.id}">${s.first_name} ${s.last_name}</option>`;
            });
        });
});

// Обновление слотов при изменении даты или специалиста
function loadSlots() {
    const sid = specialistSelect.value;
    const oid = objectSelect.value;
    const date = dateInput.value;
    if (!sid || !date || !oid) {
        slotsDiv.innerHTML = '';
        submitBtn.disabled = true;
        return;
    }
    slotsDiv.innerHTML = 'Загрузка...';
    fetch(`get_available_slots.php?specialist_id=${sid}&date=${date}&object_id=${oid}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                slotsDiv.innerHTML = `<p class="error-msg">${data.error}</p>`;
                return;
            }
            if (!data.slots.length) {
                slotsDiv.innerHTML = '<p>Нет свободного времени на выбранную дату</p>';
                submitBtn.disabled = true;
                return;
            }
            let html = '';
            data.slots.forEach(slot => {
                html += `<label style="margin-right:10px;"><input type="radio" name="slot" value="${slot}" data-datetime="${date} ${slot}:00"> ${slot}</label><br>`;
            });
            slotsDiv.innerHTML = html;
            document.querySelectorAll('input[name="slot"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    selectedSlot = this.dataset.datetime;
                    hiddenDatetime.value = selectedSlot;
                    submitBtn.disabled = false;
                });
            });
        });
}

specialistSelect.addEventListener('change', loadSlots);
dateInput.addEventListener('change', loadSlots);

// Инициализация
if (objectSelect.value) objectSelect.dispatchEvent(new Event('change'));
</script>

<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>