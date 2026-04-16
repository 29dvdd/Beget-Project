<?php
// check_admin.php — Скрипт защиты (Middleware)

// 1. Включаем доступ к сессии
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 2. Проверяем два условия:
//    А. Пользователь вообще вошел? (есть ли user_id)
//    Б. Его роль — это 'admin'?
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    ?>
    <!doctype html>
    <html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>403 - Доступ запрещен</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="alert alert-danger shadow-sm">
                <h1 class="h3 mb-3">403 - Доступ запрещен</h1>
                <p class="mb-3">Эта страница доступна только администратору.</p>
                <div class="d-flex gap-2">
                    <a class="btn btn-primary" href="index.php?page=home">На главную</a>
                    <a class="btn btn-outline-secondary" href="index.php?page=login">Войти</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}


// Если код идет дальше — значит, это Админ.
?>
