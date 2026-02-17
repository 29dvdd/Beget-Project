<?php
require __DIR__ . '/check_admin.php';
require __DIR__ . '/config/db.php';

// Метод только POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный метод запроса");
}

// CSRF защита
if (
    empty($_SESSION['csrf_token']) ||
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die("Ошибка CSRF");
}

// Получаем ID мероприятия
$id = (int)($_POST['event_id'] ?? 0);
if ($id <= 0) {
    die("Неверный ID мероприятия");
}

try {
    // Удаляем мероприятие
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        die("Мероприятие не найдено");
    }

    // Перенаправляем на главную
    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    die("Ошибка базы данных: " . htmlspecialchars($e->getMessage()));
}
