<?php
require 'check_admin.php';
require 'db.php';

$sql = "
    SELECT 
        orders.id as order_id,
        orders.created_at,
        users.email,
        products.title,
        products.price
    FROM orders
    JOIN users ON orders.user_id = users.id
    LEFT JOIN products ON orders.product_id = products.id
    ORDER BY orders.id DESC
";

$orders = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заказами</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1>Все заказы</h1>
    <a href="index.php">На главную</a>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID Заказа</th>
                <th>Дата</th>
                <th>Клиент (Email)</th>
                <th>Товар</th>
                <th>Цена</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= (int)$order['order_id'] ?></td>
                <td><?= htmlspecialchars($order['created_at']) ?></td>
                <td><?= htmlspecialchars($order['email']) ?></td>
                <td><?= htmlspecialchars($order['title'] ?? 'Товар удалён') ?></td>
                <td><?= htmlspecialchars($order['price'] !== null ? $order['price'] . ' ₽' : '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
