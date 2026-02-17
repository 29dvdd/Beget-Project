<?php
// check_admin.php — Скрипт защиты (Middleware)

// 1. Включаем доступ к сессии
session_start();

// 2. Проверяем два условия:
//    А. Пользователь вообще вошел? (есть ли user_id)
//    Б. Его роль — это 'admin'?
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    die("ДОСТУП ЗАПРЕЩЕН. <a href='index.php?page=login'>Войти</a>");
}


// Если код идет дальше — значит, это Админ.
?>