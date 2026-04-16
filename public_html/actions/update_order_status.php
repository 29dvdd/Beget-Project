<?php
require __DIR__ . '/../includes/check_admin.php';
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Неверный метод запроса');
}

if (
    empty($_SESSION['csrf_token']) ||
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    die('Ошибка CSRF');
}

$orderId = (int)($_POST['order_id'] ?? 0);
$status = $_POST['status'] ?? '';
$allowedStatuses = ['new', 'processing', 'done'];

if ($orderId <= 0 || !in_array($status, $allowedStatuses, true)) {
    $_SESSION['flash_error'] = 'Некорректные данные для изменения статуса.';
    header('Location: index.php?page=admin_orders');
    exit;
}

$stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ? AND status <> ?');
$stmt->execute([$status, $orderId, 'cancelled']);

$_SESSION['flash_success'] = 'Статус заказа обновлен.';
header('Location: index.php?page=admin_orders');
exit;
