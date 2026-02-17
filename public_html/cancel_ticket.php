<?php
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die("Method not allowed");
}

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  die("Unauthorized");
}

if (
  empty($_SESSION['csrf_token']) ||
  empty($_POST['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  http_response_code(403);
  die("CSRF error");
}

$user_id = (int)$_SESSION['user_id'];
$order_id = (int)($_POST['order_id'] ?? 0);
if ($order_id <= 0) die("Bad order id");

try {
  $pdo->beginTransaction();

  // Берём заказ и блокируем
  $stmt = $pdo->prepare("
    SELECT id, user_id, event_id, status, COALESCE(qty, quantity) AS qty
    FROM orders
    WHERE id = ?
    FOR UPDATE
  ");
  $stmt->execute([$order_id]);
  $order = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$order) throw new Exception("Заказ не найден");
  if ((int)$order['user_id'] !== $user_id && (($_SESSION['role'] ?? '') !== 'admin')) {
    throw new Exception("Нет доступа");
  }
  if ($order['status'] === 'cancelled') {
    throw new Exception("Уже отменён");
  }

  $qty = (int)$order['qty'];
  $event_id = (int)$order['event_id'];

  // Возвращаем билеты
  $updE = $pdo->prepare("UPDATE events SET available_tickets = available_tickets + ? WHERE id = ?");
  $updE->execute([$qty, $event_id]);

  // Меняем статус
  $updO = $pdo->prepare("UPDATE orders SET status='cancelled' WHERE id = ?");
  $updO->execute([$order_id]);

  $pdo->commit();
  header("Location: index.php?page=profile&ok=1");
  exit;

} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(400);
  echo "Ошибка: " . htmlspecialchars($e->getMessage());
}