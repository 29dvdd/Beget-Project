<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Главная</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">

    <h1>Добро пожаловать!</h1>

    <?php if (!empty($_SESSION['user_email'])): ?>
        <p>Вы вошли как: <strong><?= htmlspecialchars($_SESSION['user_email']) ?></strong></p>
        <a href="?page=events" class="btn btn-primary me-2">Просмотреть мероприятия</a>
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="?page=add_event" class="btn btn-success me-2">Добавить мероприятие</a>
        <?php endif; ?>
        <a href="?page=logout" class="btn btn-danger">Выйти</a>
    <?php else: ?>
        <a href="?page=login" class="btn btn-primary me-2">Вход</a>
        <a href="?page=register" class="btn btn-secondary">Регистрация</a>
    <?php endif; ?>

</div>
</body>
</html>
