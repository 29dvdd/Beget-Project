<?php
/**
 * Покупка билетов (создание заказа)
 */

session_start();
require_once __DIR__ . '/config/db.php';

// Только POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Неверный метод запроса");
}

// Только авторизованный
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die("Авторизуйтесь, чтобы купить билет");
}

// CSRF
if (
    empty($_SESSION['csrf_token']) ||
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    die("Ошибка CSRF");
}

$user_id  = (int)$_SESSION['user_id'];
$event_id = (int)($_POST['event_id'] ?? 0);

// ВАЖНО: в форме может быть name="quantity", но мы приводим к qty
$qty = (int)($_POST['quantity'] ?? ($_POST['qty'] ?? 0));

if ($event_id <= 0) die("Неверное мероприятие");
if ($qty <= 0) die("Некорректное количество");

try {
    $pdo->beginTransaction();

    // Блокируем событие
    $stmt = $pdo->prepare("SELECT price, available_tickets FROM events WHERE id = ? FOR UPDATE");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) throw new Exception("Мероприятие не найдено");

    $price = (float)$event['price'];
    $available = (int)$event['available_tickets'];

    if ($available < $qty) {
        throw new Exception("Недостаточно билетов. Осталось: {$available}");
    }

    // Списываем билеты
    $upd = $pdo->prepare("UPDATE events SET available_tickets = available_tickets - ? WHERE id = ?");
    $upd->execute([$qty, $event_id]);

    // ✅ INSERT под поле qty
    // Минимальный вариант (если в orders только эти поля)
    // INSERT INTO orders (user_id, event_id, qty, status, created_at)
    // Если у тебя поле qty называется иначе — скажи
    $ins = $pdo->prepare("
        INSERT INTO orders (user_id, event_id, qty, status, created_at)
        VALUES (?, ?, ?, 'paid', NOW())
    ");
    $ins->execute([$user_id, $event_id, $qty]);

    $pdo->commit();

    header("Location: index.php?page=profile&ok=1");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo "Ошибка покупки: " . htmlspecialchars($e->getMessage());
}