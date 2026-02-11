<?php
session_start();
require_once 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Доступ запрещен');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['poster'])) {
    $event_id = (int)$_POST['event_id'];
    
    // Проверяем существование мероприятия
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        die("Мероприятие не найдено");
    }
    
    $file = $_FILES['poster'];
    
    // Проверка ошибок загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Ошибка загрузки файла: " . $file['error']);
    }
    
    // Проверка типа файла
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        die("Ошибка: Можно загружать только картинки (JPG, PNG, GIF).");
    }
    
    // Проверка размера файла (5MB максимум)
    if ($file['size'] > 5 * 1024 * 1024) {
        die("Ошибка: Файл слишком большой. Максимальный размер: 5MB");
    }
    
    // Создаем папку для постеров, если её нет
    $uploadDir = 'uploads/posters/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Генерируем безопасное имя файла
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = uniqid('poster_') . '.' . $extension;
    $destination = $uploadDir . $newName;
    
    // Перемещаем файл
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Обновляем путь в базе данных
        $stmt = $pdo->prepare("UPDATE events SET poster_url = ? WHERE id = ?");
        $stmt->execute([$destination, $event_id]);
        
        // Перенаправляем обратно на страницу редактирования
        header("Location: ?page=edit_event&id=$event_id&success=1");
        exit;
    } else {
        die("Не удалось сохранить файл. Проверьте права доступа к папке uploads/posters/");
    }
} else {
    die("Неверный метод запроса");
}
?>
