<?php
require 'check_admin.php';
require 'db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Получаем данные мероприятия
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать мероприятие</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width:720px;">

    <h3 class="mb-3">Редактировать мероприятие</h3>

    <form method="post" action="update_product.php" enctype="multipart/form-data">
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image_url']) ?>">

        <div class="mb-3">
            <label class="form-label">Название *</label>
            <input class="form-control" name="title" required
                   value="<?= htmlspecialchars($product['title']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Цена (₽) *</label>
            <input type="number" step="0.01" class="form-control" name="price"
                   value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Количество билетов *</label>
            <input type="number" class="form-control" name="available_tickets"
                   value="<?= (int)$product['available_tickets'] ?>" min="0" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Дата мероприятия *</label>
            <input type="date" class="form-control" name="event_date"
                   value="<?= htmlspecialchars($product['event_date'] ?? date('Y-m-d')) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Адрес проведения</label>
            <input class="form-control" type="text" name="address" 
                   placeholder="Например: г. Москва, ул. Тверская, д. 1"
                   value="<?= htmlspecialchars($product['address'] ?? '') ?>">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Широта (latitude)</label>
                <input class="form-control" type="number" step="0.00000001" name="latitude" 
                       placeholder="55.7558"
                       value="<?= htmlspecialchars($product['latitude'] ?? '') ?>">
                <small class="text-muted">Для отображения на карте</small>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Долгота (longitude)</label>
                <input class="form-control" type="number" step="0.00000001" name="longitude" 
                       placeholder="37.6173"
                       value="<?= htmlspecialchars($product['longitude'] ?? '') ?>">
                <small class="text-muted">Для отображения на карте</small>
            </div>
        </div>

        <!-- Текущее изображение -->
        <div class="mb-3">
            <label class="form-label">Текущая афиша</label><br>
            <?php if ($product['image_url']): ?>
                <img src="<?= htmlspecialchars($product['image_url']) ?>"
                     style="max-height:150px;border-radius:6px;">
            <?php else: ?>
                <span class="text-muted">Нет изображения</span>
            <?php endif; ?>
        </div>

        <!-- Загрузка нового файла -->
        <div class="mb-3">
            <label class="form-label">Загрузить новую афишу (JPG / PNG / GIF, макс. 5MB)</label>
            <input type="file" class="form-control" name="image_file" accept="image/jpeg,image/png,image/gif">
            <small class="text-muted">Только JPG, PNG, GIF. Максимальный размер: 5MB</small>
        </div>

        <button class="btn btn-success w-100">Сохранить изменения</button>
    </form>
</div>
</body>
</html>
