<?php
require 'check_admin.php';
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $price = $_POST['price'] ?? '';
    $desc  = trim($_POST['description'] ?? '');
    $img   = trim($_POST['image_url'] ?? '');

    if ($title === '' || $price === '') {
        $message = '<div class="alert alert-danger">Заполните название и цену!</div>';
    } elseif (!is_numeric($price) || (float)$price <= 0) {
        $message = '<div class="alert alert-danger">Цена должна быть положительным числом!</div>';
    } else {
        $img = ($img !== '') ? $img : null;

        $sql = "INSERT INTO products (title, description, price, image_url) 
                VALUES (:t, :d, :p, :i)";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':t' => $title,
                ':d' => $desc,
                ':p' => $price,
                ':i' => $img
            ]);

            // лучше редирект, чтобы при обновлении страницы не дублировать добавление
            header("Location: add_item.php?ok=1");
            exit;

        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Ошибка БД: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

if (isset($_GET['ok'])) {
    $message = '<div class="alert alert-success">Товар успешно добавлен!</div>';
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить товар</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width: 720px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Добавить товар</h3>
        <a class="btn btn-outline-secondary btn-sm" href="admin_panel.php">Назад</a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Название *</label>
                    <input class="form-control" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Цена (₽) *</label>
                    <input class="form-control" name="price" type="number" step="0.01" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Ссылка на изображение</label>
                    <input class="form-control" name="image_url" value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>">
                </div>

                <button class="btn btn-success w-100">Добавить</button>
            </form>
        </div>
    </div>

</div>
</body>
</html>
