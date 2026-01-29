<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

// 1) Только для авторизованных
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2) Только POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный метод запроса.");
}

// 3) CSRF проверка
$session_token = $_SESSION['csrf_token'] ?? '';
$post_token    = $_POST['csrf_token'] ?? '';

if (
    $session_token === '' ||
    $post_token === '' ||
    !hash_equals($session_token, $post_token)
) {
    die("Ошибка безопасности (CSRF).");
}

// 4) Данные
$order_id = (int)($_POST['order_id'] ?? 0);
$user_id  = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    die("Некорректный заказ.");
}

// 5) ANTI-IDOR + LOGIC CHECK
// Удаляем ТОЛЬКО:
// — свой заказ
// — только если статус = new
$sql = "
    DELETE FROM orders 
    WHERE id = ? 
      AND user_id = ? 
      AND status = 'new'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id, $user_id]);

if ($stmt->rowCount() === 0) {
    die("Заказ не найден или не может быть удалён.");
}

// 6) Успех
header("Location: profile.php");
exit;