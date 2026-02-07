<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
$user_id  = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    die("Заказ не найден.");
}

// Anti-IDOR: ищем заказ по id И по владельцу
$sql = "
    SELECT 
        orders.id as order_id,
        orders.created_at,
        orders.status,
        products.title,
        products.description,
        products.price,
        products.image_url
    FROM orders
    JOIN products ON orders.product_id = products.id
    WHERE orders.id = ? AND orders.user_id = ?
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Заказ не найден или у вас нет прав на его просмотр.");
}

// Выбираем цвет статуса
$status_color = 'secondary';
if ($order['status'] === 'new') $status_color = 'primary';
if ($order['status'] === 'processing') $status_color = 'warning';
if ($order['status'] === 'done') $status_color = 'success';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ #<?= (int)$order['order_id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="profile.php" class="btn btn-outline-secondary btn-sm">← Назад в профиль</a>
        <?php if ($order['status'] === 'new'): ?>
            <form method="POST" action="delete_order.php" onsubmit="return confirm('Удалить заказ?');">
                <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Удалить заказ</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h4 class="mb-1">Заказ #<?= (int)$order['order_id'] ?></h4>
            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></small>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <?php $img = $order['image_url'] ?: 'https://via.placeholder.com/300x200?text=Мероприятие'; ?>
                    <img src="<?= htmlspecialchars($img) ?>" class="img-fluid rounded" alt="Фото">
                </div>
                <div class="col-md-8">
                    <h5><?= htmlspecialchars($order['title']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($order['description'] ?? '') ?></p>
                    <p class="fw-bold">Цена: <?= number_format((float)$order['price'], 0, '', ' ') ?> ₽</p>
                    <p>Статус: <span class="badge bg-<?= $status_color ?>"><?= htmlspecialchars($order['status']) ?></span></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
