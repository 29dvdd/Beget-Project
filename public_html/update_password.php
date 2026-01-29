<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

// 1) Доступ только для авторизованных
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

if ($session_token === '' || $post_token === '' || !hash_equals($session_token, $post_token)) {
    die("Ошибка безопасности: Неверный CSRF-токен!");
}

// 4) Данные формы
$user_id = (int)$_SESSION['user_id'];
$old  = (string)($_POST['old_password'] ?? '');
$new  = (string)($_POST['new_password'] ?? '');
$new2 = (string)($_POST['new_password2'] ?? '');

// 5) Валидации
if ($new !== $new2) {
    header("Location: change_password.php?err=" . urlencode("Новые пароли не совпадают"));
    exit;
}

if (strlen($new) < 8) {
    header("Location: change_password.php?err=" . urlencode("Новый пароль должен быть минимум 8 символов"));
    exit;
}

try {
    // 6) Получаем текущий хэш из БД
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: change_password.php?err=" . urlencode("Пользователь не найден"));
        exit;
    }

    // 7) Проверяем старый пароль
    if (!password_verify($old, $user['password_hash'])) {
        header("Location: change_password.php?err=" . urlencode("Старый пароль неверный"));
        exit;
    }

    // 8) Хэшируем новый пароль
    $new_hash = password_hash($new, PASSWORD_DEFAULT);

    // 9) Обновляем в БД
    $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $upd->execute([$new_hash, $user_id]);

    // 10) На всякий случай обновим CSRF токен (хорошая практика)
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // 11) Успех
    header("Location: change_password.php?ok=1");
    exit;

} catch (PDOException $e) {
    header("Location: change_password.php?err=" . urlencode("Ошибка БД"));
    exit;
}