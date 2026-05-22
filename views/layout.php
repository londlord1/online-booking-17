<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление справочниками</title>
    <style>
        /* Общие стили */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        /* Навигация */
        nav {
            background: #1e293b;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        nav a {
            color: #e2e8f0;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
            transition: color 0.2s;
        }
        nav a:hover { color: #38bdf8; }

        /* Заголовки */
        h1 {
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }

        /* Flash-сообщения */
        .flash {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        .flash.success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .flash.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Поисковая строка */
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .search-form input[type="text"] {
            flex: 1;
            min-width: 200px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 1rem;
        }
        .search-form button,
        .search-form a {
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            border: none;
            font-size: 1rem;
        }
        .search-form button { background: #0ea5e9; color: #fff; }
        .search-form button:hover { background: #0284c7; }
        .search-form a { background: #e2e8f0; color: #334155; }
        .search-form a:hover { background: #cbd5e1; }

        /* Кнопки */
        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .btn-primary { background: #0ea5e9; color: #fff; }
        .btn-primary:hover { background: #0284c7; box-shadow: 0 2px 8px rgba(14,165,233,0.3); }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }
        .btn-group { display: flex; gap: 8px; flex-wrap: wrap; }

        /* Таблица */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 1px 6px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #f8fafc;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
        }
        th a {
            text-decoration: none;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        th a:hover { color: #0ea5e9; }
        tr:hover td { background: #f1f5f9; }

        /* Пагинация */
        .pagination {
            list-style: none;
            display: flex;
            gap: 6px;
            padding: 0;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .pagination li a,
        .pagination li strong {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            background: #e2e8f0;
            color: #334155;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .pagination li strong { background: #0ea5e9; color: #fff; }
        .pagination li a:hover { background: #cbd5e1; }

        /* Формы */
        form label {
            display: block;
            margin-top: 16px;
            font-weight: 500;
            color: #334155;
        }
        .required::after {
            content: " *";
            color: #ef4444;
        }
        form input, form select, form textarea {
            width: 100%;
            max-width: 500px;
            padding: 10px 14px;
            margin-top: 4px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        form input:focus, form select:focus, form textarea:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,0.2);
        }
        .error-msg {
            color: #ef4444;
            font-size: 0.9rem;
            margin-top: 4px;
            display: block;
        }
        input.error, select.error {
            border-color: #ef4444;
            background-color: #fef2f2;
        }
        form button[type="submit"] {
            margin-top: 20px;
        }
        form .cancel-link {
            display: inline-block;
            margin-left: 12px;
            vertical-align: middle;
            color: #64748b;
            text-decoration: none;
        }
        form .cancel-link:hover { color: #334155; }

        /* Удаление */
        .delete-confirm p {
            font-size: 1.1rem;
            margin: 20px 0;
        }
        .delete-confirm .btn-danger {
            margin-right: 10px;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            body { padding: 10px; }
            .container { padding: 15px; }
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { display: none; }
            td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
                border-bottom: 1px solid #e2e8f0;
            }
            td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #1e293b;
                margin-right: 15px;
            }
            .btn-group { flex-direction: column; gap: 5px; }
            .search-form { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav>
   <a href="?entity=client&action=list">Клиенты</a>
    <a href="?entity=object&action=list">Объекты</a>
    <a href="?entity=specialist&action=list">Специалисты</a>
    <a href="?entity=appointment&action=list">Записи</a>
    <a href="?entity=appointment&action=reports">Отчёты</a>
        </nav>
        <?php if ($flash = getFlash()): ?>
            <div class="flash <?= $flash['type'] ?>"><?= h($flash['message']) ?></div>
        <?php endif; ?>
        <?= $content ?? '' ?>
    </div>
</body>
</html>