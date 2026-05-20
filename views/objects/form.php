<?php ob_start();
$isEdit = ($action === 'edit');
$title = $isEdit ? 'Редактирование объекта' : 'Добавление объекта';
?>
<h1><?= $title ?></h1>

<form method="post" action="?entity=object&action=<?= $isEdit ? 'update&id='.$_GET['id'] : 'store' ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <label class="required">Адрес</label>
    <input type="text" name="address" required value="<?= old('address') ?>" class="<?= error_class($errors, 'address') ?>">
    <?= error_text($errors, 'address') ?>

    <label class="required">Тип объекта</label>
    <select name="type" required class="<?= error_class($errors, 'type') ?>">
        <option value="">-- выберите --</option>
        <?php foreach (['Квартира','Дом','Офис','Участок','Коммерческое помещение'] as $t): ?>
            <option value="<?= $t ?>" <?= old('type')===$t ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
    </select>
    <?= error_text($errors, 'type') ?>

    <label>Площадь (м²)</label>
    <input type="number" step="0.01" name="area" value="<?= old('area') ?>" class="<?= error_class($errors, 'area') ?>">
    <?= error_text($errors, 'area') ?>

    <label class="required">Цена (руб)</label>
    <input type="number" step="0.01" name="price" required value="<?= old('price') ?>" class="<?= error_class($errors, 'price') ?>">
    <?= error_text($errors, 'price') ?>

    <label>Описание</label>
    <textarea name="description" rows="4"><?= old('description') ?></textarea>

    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Сохранить изменения' : 'Создать' ?></button>
    <a href="?entity=object&action=list" class="cancel-link">Отмена</a>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>