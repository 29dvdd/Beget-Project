<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Авторизуйтесь");
}

// CSRF
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die("CSRF ошибка");
}

$user_id  = (int)$_SESSION['user_id'];
$event_id = (int)$_POST['event_id'];
$qty      = (int)$_POST['quantity'];

if ($qty <= 0) {
    die("Некорректное количество");
}

// 1️⃣ Получаем актуальный остаток из БД
$stmt = $pdo->prepare("
    SELECT price, available_tickets 
    FROM products 
    WHERE id = ?
");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    die("Мероприятие не найдено");
}

// 2️⃣ LOGIC CHECK
if ($event['available_tickets'] < $qty) {
    die("Ошибка: осталось только {$event['available_tickets']} билетов");
}

// 3️⃣ Обновляем остаток
$update = $pdo->prepare("
    UPDATE products 
    SET available_tickets = available_tickets - ? 
    WHERE id = ?
");
$update->execute([$qty, $event_id]);

// 4️⃣ Создаем заказ
$insert = $pdo->prepare("
    INSERT INTO orders (user_id, product_id, quantity, created_at)
    VALUES (?, ?, ?, NOW())
");
$insert->execute([$user_id, $event_id, $qty]);

header("Location: profile.php");
exit;
