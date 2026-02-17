<?php
require __DIR__ . '/check_admin.php';
require __DIR__ . '/config/db.php';

$message = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = '<div class="alert alert-danger">Ошибка CSRF. Обновите страницу и попробуйте снова.</div>';
    }

    $title   = trim($_POST['title'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $eventDate = $_POST['event_date'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $tickets = (int)($_POST['available_tickets'] ?? 0);
    $imgUrl  = null;

    if ($title === '' || $eventDate === '') {
        $message = '<div class="alert alert-danger">Заполните название и дату!</div>';
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
                "INSERT INTO events (title, description, event_date, venue, price, available_tickets, poster_url)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$title, $desc, $eventDate, $venue ?: null, $price, $tickets, $imgUrl]);

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
        <a href="index.php" class="btn btn-outline-secondary btn-sm">Назад</a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="mb-3">
                    <label class="form-label">Название *</label>
                    <input class="form-control" name="title" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea class="form-control" name="description" rows="4"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Дата мероприятия *</label>
                    <input class="form-control" type="date" name="event_date" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Место проведения</label>
                    <input class="form-control" type="text" name="venue" 
                           placeholder="Например: Концертный зал Олимпийский">
                </div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Цена билета (₽)</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="price" value="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Доступно билетов</label>
                        <input class="form-control" type="number" min="0" name="available_tickets" value="0">
                    </div>
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
