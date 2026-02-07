<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Доступ запрещён");
}

$product_id = (int)($_GET['id'] ?? 0);
if ($product_id <= 0) {
    die("Товар не найден");
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Товар не найден");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать товар</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<h2>Редактирование товара</h2>

<form method="POST" action="update_product.php">
    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
    <input type="hidden" name="csrf_token"
           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <div class="mb-3">
        <label>Название</label>
        <input class="form-control" name="title"
               value="<?= htmlspecialchars($product['title']) ?>" required>
    </div>

    <div class="mb-3">
        <label>Описание</label>
        <textarea class="form-control" name="description"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="mb-3">
        <label>Цена</label>
        <input type="number" class="form-control" name="price"
               value="<?= (float)$product['price'] ?>" required>
    </div>

    <button class="btn btn-success">Сохранить</button>
    <a href="index.php" class="btn btn-secondary">Отмена</a>
</form>

</body>
</html>
