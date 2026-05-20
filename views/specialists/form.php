<?php ob_start();
$isEdit = ($action === 'edit');
$title = $isEdit ? 'Редактирование специалиста' : 'Добавление специалиста';
?>
<h1><?= $title ?></h1>

<form method="post" action="?entity=specialist&action=<?= $isEdit ? 'update&id='.$_GET['id'] : 'store' ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <label class="required">Имя</label>
    <input type="text" name="first_name" required value="<?= old('first_name') ?>" class="<?= error_class($errors, 'first_name') ?>">
    <?= error_text($errors, 'first_name') ?>

    <label class="required">Фамилия</label>
    <input type="text" name="last_name" required value="<?= old('last_name') ?>" class="<?= error_class($errors, 'last_name') ?>">
    <?= error_text($errors, 'last_name') ?>

    <label class="required">Телефон</label>
    <input type="tel" name="phone" required pattern="\+?\d[\d\-\(\) ]{6,}" value="<?= old('phone') ?>" class="<?= error_class($errors, 'phone') ?>">
    <?= error_text($errors, 'phone') ?>

    <label class="required">Email</label>
    <input type="email" name="email" required value="<?= old('email') ?>" class="<?= error_class($errors, 'email') ?>">
    <?= error_text($errors, 'email') ?>

    <label>Специализация</label>
    <select name="specialization">
        <option value="">-- выберите --</option>
        <?php foreach (['Жилая недвижимость','Коммерческая недвижимость','Аренда','Продажа'] as $spec): ?>
            <option value="<?= $spec ?>" <?= old('specialization')===$spec ? 'selected' : '' ?>><?= $spec ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Сохранить изменения' : 'Создать' ?></button>
    <a href="?entity=specialist&action=list" class="cancel-link">Отмена</a>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>