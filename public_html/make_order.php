<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Сначала войдите в систему! <a href='login.php'>Вход</a>");
}

$product_id = (int)($_GET['id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($product_id <= 0) {
    die("Неверный товар.");
}

// Проверка: существует ли товар?
$check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$check->execute([$product_id]);
$exists = $check->fetch();

if (!$exists) {
    die("Ошибка: попытка заказать несуществующий товар! Ваш IP записан.");
}

// Создаём заказ
$stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id) VALUES (?, ?)");

try {
    $stmt->execute([$user_id, $product_id]);
    echo "Заказ успешно оформлен! <a href='index.php'>Вернуться</a>";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
