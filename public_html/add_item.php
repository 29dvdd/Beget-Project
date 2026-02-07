<?php
require 'check_admin.php';
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $price   = $_POST['price'] ?? '';
    $desc    = trim($_POST['description'] ?? '');
    $tickets = (int)($_POST['available_tickets'] ?? 0);
    $eventDate = $_POST['event_date'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
    $imgUrl  = null;

    if ($title === '' || $price === '' || $eventDate === '') {
        $message = '<div class="alert alert-danger">Заполните название, цену и дату!</div>';
    } elseif (!is_numeric($price) || $price <= 0) {
        $message = '<div class="alert alert-danger">Цена некорректна</div>';
    } elseif ($tickets < 0) {
        $message = '<div class="alert alert-danger">Билеты не могут быть отрицательными</div>';
    } else {
        // ===== Загрузка файла афиши =====
        if (!empty($_FILES['poster']['name'])) {
            $allowed = ['image/jpeg','image/png','image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['poster']['type'], $allowed)) {
                $message = '<div class="alert alert-danger">Только JPG / PNG / GIF</div>';
            } elseif ($_FILES['poster']['size'] > $max_size) {
                $message = '<div class="alert alert-danger">Файл слишком большой (максимум 5MB)</div>';
            } else {
                // Создаем папку uploads если её нет
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0755, true);
                }
                
                $ext = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
                $name = 'uploads/poster_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['poster']['tmp_name'], $name)) {
                    $imgUrl = $name;
                } else {
                    $message = '<div class="alert alert-danger">Ошибка загрузки файла</div>';
                }
            }
        }

        // ===== Если файл не загружен — используем ссылку =====
        if (!$imgUrl && !empty($_POST['image_url'])) {
            $imgUrl = trim($_POST['image_url']);
        }

        if ($message === '') {
            $stmt = $pdo->prepare(
                "INSERT INTO products (title, description, price, available_tickets, image_url, event_date, address, latitude, longitude, is_published)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)"
            );
            $stmt->execute([$title, $desc, $price, $tickets, $imgUrl, $eventDate, $address ?: null, $latitude, $longitude]);

            header("Location: add_item.php?ok=1");
            exit;
        }
    }
}

if (isset($_GET['ok'])) {
    $message = '<div class="alert alert-success">Мероприятие добавлено!</div>';
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить мероприятие</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4" style="max-width: 720px;">

    <div class="d-flex justify-content-between mb-3">
        <h3>Добавить мероприятие</h3>
        <a href="admin_panel.php" class="btn btn-outline-secondary btn-sm">Назад</a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label">Название *</label>
                    <input class="form-control" name="title" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea class="form-control" name="description" rows="4"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Цена (₽) *</label>
                    <input class="form-control" type="number" step="0.01" name="price" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Количество билетов *</label>
                    <input class="form-control" type="number" name="available_tickets" min="0" value="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Дата мероприятия *</label>
                    <input class="form-control" type="date" name="event_date" required value="<?= htmlspecialchars($_POST['event_date'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Адрес проведения</label>
                    <input class="form-control" type="text" name="address" 
                           placeholder="Например: г. Москва, ул. Тверская, д. 1"
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Широта (latitude)</label>
                        <input class="form-control" type="number" step="0.00000001" name="latitude" 
                               placeholder="55.7558"
                               value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>">
                        <small class="text-muted">Для отображения на карте</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Долгота (longitude)</label>
                        <input class="form-control" type="number" step="0.00000001" name="longitude" 
                               placeholder="37.6173"
                               value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>">
                        <small class="text-muted">Для отображения на карте</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Загрузить афишу (файл, макс. 5MB)</label>
                    <input class="form-control" type="file" name="poster" accept="image/jpeg,image/png,image/gif">
                    <small class="text-muted">Только JPG, PNG, GIF. Максимальный размер: 5MB</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">ИЛИ ссылка на изображение</label>
                    <input class="form-control" name="image_url">
                </div>

                <button class="btn btn-success w-100">Добавить мероприятие</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
