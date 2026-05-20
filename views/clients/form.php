<?php ob_start();
$isEdit = ($action === 'edit');
$title = $isEdit ? 'Редактирование клиента' : 'Добавление клиента';
?>
<h1><?= $title ?></h1>

<form method="post" action="?entity=client&action=<?= $isEdit ? 'update&id='.$_GET['id'] : 'store' ?>">
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

    <label>Дата рождения</label>
    <input type="date" name="birth_date" value="<?= old('birth_date') ?>" class="<?= error_class($errors, 'birth_date') ?>">
    <?= error_text($errors, 'birth_date') ?>

    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Сохранить изменения' : 'Создать' ?></button>
    <a href="?entity=client&action=list" class="cancel-link">Отмена</a>
</form>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>