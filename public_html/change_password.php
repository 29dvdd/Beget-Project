<?php
session_start();
require 'db.php';

// Доступ только для авторизованных
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// CSRF токен (если вдруг не создался при логине)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = '';
if (isset($_GET['ok']) && $_GET['ok'] === '1') {
    $success = "Пароль успешно изменён.";
}

$error = '';
if (isset($_GET['err'])) {
    // Показываем “мягко”, без раскрытия деталей
    $error = "Ошибка: " . htmlspecialchars($_GET['err']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Смена пароля</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-light bg-light px-4 mb-4 shadow-sm">
    <a class="navbar-brand mb-0 h1" href="index.php">Мой Магазин</a>
    <div>
        <a href="profile.php" class="btn btn-outline-primary btn-sm me-2">Мои заказы</a>
        <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>
    </div>
</nav>

<div class="container" style="max-width: 650px;">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Смена пароля</h4>
            <a href="profile.php" class="btn btn-outline-secondary btn-sm">Назад</a>
        </div>
        <div class="card-body">

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form action="update_password.php" method="POST">
                <!-- CSRF токен -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="mb-3">
                    <label class="form-label">Старый пароль</label>
                    <input type="password" name="old_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Новый пароль (минимум 8 символов)</label>
                    <input type="password" name="new_password" class="form-control" required minlength="8">
                </div>

                <div class="mb-3">
                    <label class="form-label">Повтор нового пароля</label>
                    <input type="password" name="new_password2" class="form-control" required minlength="8">
                </div>

                <button type="submit" class="btn btn-primary w-100">Сохранить</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>