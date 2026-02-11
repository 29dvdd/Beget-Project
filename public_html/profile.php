<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Получаем заказы пользователя
$sql = "
    SELECT 
        orders.id as order_id, 
        orders.created_at, 
        orders.status, 
        products.title, 
        products.price,
        products.image_url
    FROM orders 
    JOIN products ON orders.product_id = products.id 
    WHERE orders.user_id = ? 
    ORDER BY orders.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Мой Магазин</a>
        <div class="d-flex">
            <span class="navbar-text text-white me-3">
                Вы вошли как: <b><?= htmlspecialchars($_SESSION['email'] ?? 'User') ?></b>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Мои заказы</h2>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="change_password.php">Сменить пароль</a>
                <a class="btn btn-outline-secondary btn-sm" href="index.php">Каталог</a>
            </div>
        </div>
        <div class="card-body">

            <?php if ($my_orders): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>№</th>
                            <th>Дата</th>
                            <th>Мероприятие</th>
                            <th>Цена</th>
                            <th>Статус</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($my_orders as $order): ?>
                            <tr>
                                <td>#<?= (int)$order['order_id'] ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                <td><b><?= htmlspecialchars($order['title']) ?></b></td>
                                <td><?= number_format((float)$order['price'], 0, '', ' ') ?> ₽</td>
                                <td>
                                    <?php
                                    $status_color = 'secondary';
                                    if ($order['status'] === 'new') $status_color = 'primary';
                                    if ($order['status'] === 'processing') $status_color = 'warning';
                                    if ($order['status'] === 'done') $status_color = 'success';
                                    ?>
                                    <span class="badge bg-<?= $status_color ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end d-flex gap-2 justify-content-end">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="order_details.php?id=<?= (int)$order['order_id'] ?>">
                                        Подробнее
                                    </a>

                                    <?php if ($order['status'] === 'new'): ?>
                                        <form method="POST" action="delete_order.php"
                                              onsubmit="return confirm('Удалить заказ?');">
                                            <input type="hidden" name="order_id"
                                                   value="<?= (int)$order['order_id'] ?>">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Удалить
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <h5 class="text-muted">У вас пока нет заказов.</h5>
                    <a href="index.php" class="btn btn-primary mt-3">Перейти в каталог</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
