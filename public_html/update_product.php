<?php
require __DIR__ . '/check_admin.php';
require __DIR__ . '/config/db.php';

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Неверный метод запроса");
}

// CSRF защита
if (
    empty($_SESSION['csrf_token']) ||
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    die("Ошибка CSRF");
}

// Получаем данные из POST
$id         = (int)($_POST['event_id'] ?? 0);
$title      = trim($_POST['title'] ?? '');
$desc       = trim($_POST['description'] ?? '');
$event_date = $_POST['event_date'] ?? '';
$venue      = trim($_POST['venue'] ?? '');
$price      = (float)($_POST['price'] ?? 0);
$tickets    = (int)($_POST['available_tickets'] ?? 0);
$image      = $_POST['current_image'] ?? null;

// Валидация данных
if ($id <= 0 || $title === '' || $event_date === '') {
    http_response_code(400);
    die("Ошибка данных");
}

// Работа с изображением
if (!empty($_FILES['image_file']['name'])) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($_FILES['image_file']['type'], $allowed)) {
        die("Недопустимый формат файла. Только JPG, PNG или GIF.");
    } elseif ($_FILES['image_file']['size'] > $max_size) {
        die("Файл слишком большой (максимум 5MB).");
    } else {
        // Создаем папку uploads если её нет
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }
        
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/poster_' . uniqid() . '.' . $ext;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $filename)) {
            $image = $filename;
        } else {
            die("Ошибка загрузки файла.");
        }
    }
}

// Обновление записи в БД
$stmt = $pdo->prepare("
    UPDATE events
    SET title = ?, description = ?, event_date = ?, venue = ?, price = ?, available_tickets = ?, poster_url = ?
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$title, $desc, $event_date, $venue ?: null, $price, $tickets, $image, $id]);

// Редирект обратно на главную
header("Location: index.php");
exit;
