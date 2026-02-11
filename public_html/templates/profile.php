<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1>Добро пожаловать, <?= htmlspecialchars($_SESSION['email']) ?>!</h1>

    <p><strong>Роль:</strong> <?= htmlspecialchars($_SESSION['role']) ?></p>

    <div class="mt-3">
        <a href="?page=products" class="btn btn-primary me-2">Мероприятия</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="?page=add_product" class="btn btn-success me-2">Добавить мероприятие</a>
        <?php endif; ?>
        <a href="?page=orders" class="btn btn-warning me-2">Мои заказы</a>
        <a href="?page=logout" class="btn btn-danger">Выйти</a>
    </div>
</div>
</body>
</html>
