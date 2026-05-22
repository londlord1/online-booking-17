<?php ob_start(); ?>
<h1>Отчёты</h1>

<h2>Ежедневная сводка за месяц</h2>
<form method="get" class="search-form">
    <input type="hidden" name="entity" value="appointment">
    <input type="hidden" name="action" value="reports">
    <label>Месяц: <input type="month" name="month_year" value="<?= h($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT)) ?>" onchange="this.form.submit()"></label>
</form>
<table>
    <tr><th>Дата</th><th>Количество записей</th><th>Выручка (руб)</th></tr>
    <?php foreach ($dailyReport as $row): ?>
    <tr>
        <td><?= h($row['day']) ?></td>
        <td><?= h($row['total_appointments']) ?></td>
        <td><?= h($row['total_revenue']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="?entity=appointment&action=exportReport&type=daily&month=<?= $month ?>&year=<?= $year ?>" class="btn btn-sm btn-secondary">Экспорт CSV</a>

<h2>Рейтинг специалистов за период</h2>
<form method="get" class="search-form">
    <input type="hidden" name="entity" value="appointment">
    <input type="hidden" name="action" value="reports">
    <label>С: <input type="date" name="date_from" value="<?= h($dateFrom) ?>" onchange="this.form.submit()"></label>
    <label>По: <input type="date" name="date_to" value="<?= h($dateTo) ?>" onchange="this.form.submit()"></label>
</form>
<table>
    <tr><th>Специалист</th><th>Количество записей</th><th>Выручка (руб)</th></tr>
    <?php foreach ($specialistReport as $row): ?>
    <tr>
        <td><?= h($row['first_name'].' '.$row['last_name']) ?></td>
        <td><?= h($row['appointments_count']) ?></td>
        <td><?= h($row['total_revenue']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="?entity=appointment&action=exportReport&type=specialist&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>" class="btn btn-sm btn-secondary">Экспорт CSV</a>

<h2>Отменённые записи за период</h2>
<table>
    <tr><th>Дата</th><th>Количество отмен</th></tr>
    <?php foreach ($cancelledReport as $row): ?>
    <tr>
        <td><?= h($row['day']) ?></td>
        <td><?= h($row['cancelled_count']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="?entity=appointment&action=exportReport&type=cancelled&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>" class="btn btn-sm btn-secondary">Экспорт CSV</a>

<?php $content = ob_get_clean(); require __DIR__ . '/../layout.php'; ?>