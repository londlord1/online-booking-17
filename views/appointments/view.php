<?php ob_start();
$statusColors = [
    'ожидает' => '#f0ad4e',
    'подтверждена' => '#5cb85c',
    'завершена' => '#6c757d',
    'отменена' => '#d9534f'
];
?>
<h1>Детали записи #<?= h($appointment['id']) ?></h1>
<p><strong>Код бронирования:</strong> <?= generateBookingCode($appointment['id']) ?></p>
<p><strong>Дата и время:</strong> <?= h($appointment['date_time']) ?></p>
<p><strong>Клиент:</strong> <?= h($appointment['client_fn'].' '.$appointment['client_ln']) ?> (тел. <?= h($appointment['client_phone']) ?>)</p>
<p><strong>Объект:</strong> <?= h($appointment['object_address']) ?> (тип: <?= h($appointment['object_type']) ?>, цена: <?= h($appointment['object_price']) ?> руб.)</p>
<p><strong>Специалист:</strong> <?= h($appointment['spec_fn'].' '.$appointment['spec_ln']) ?></p>
<p><strong>Статус:</strong> <span style="background:<?= $statusColors[$appointment['status']] ?>;color:#fff;padding:4px 8px;border-radius:4px;"><?= h($appointment['status']) ?></span></p>

<div class="btn-group" style="margin-top:20px;">
    <?php if ($appointment['status'] == 'ожидает' || $appointment['status'] == 'подтверждена'): ?>
        <a href="?entity=appointment&action=reschedule&id=<?= $appointment['id'] ?>" class="btn btn-primary">Перенести</a>
        <a href="?entity=appointment&action=cancel&id=<?= $appointment['id'] ?>" class="btn btn-danger" onclick="return confirm('Отменить запись?')">Отменить</a>
    <?php endif; ?>
    <a href="?entity=appointment&action=list" class="btn btn-secondary">Назад к списку</a>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>