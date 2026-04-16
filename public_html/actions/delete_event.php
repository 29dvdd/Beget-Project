<?php
require __DIR__ . '/../includes/check_admin.php';
require __DIR__ . '/../config/db.php';

function starts_with($haystack, $needle)
{
    return $needle === '' || strpos($haystack, $needle) === 0;
}

function redirect_after_delete()
{
    header('Location: index.php?page=home');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Неверный метод запроса');
}

if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    die('Ошибка CSRF');
}

$id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    die('Неверный ID мероприятия');
}

try {
    $posterStmt = $pdo->prepare('SELECT poster_url FROM events WHERE id = ? LIMIT 1');
    $posterStmt->execute(array($id));
    $poster = $posterStmt->fetchColumn();

    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ? LIMIT 1');
    $stmt->execute(array($id));

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        die('Мероприятие не найдено');
    }

    if (!empty($poster) && starts_with($poster, 'uploads/')) {
        $posterPath = __DIR__ . '/../' . $poster;
        if (is_file($posterPath)) {
            @unlink($posterPath);
        }
    }

    $_SESSION['flash_success'] = 'Мероприятие удалено.';
    redirect_after_delete();
} catch (PDOException $e) {
    http_response_code(500);
    die('Не удалось удалить мероприятие');
}
