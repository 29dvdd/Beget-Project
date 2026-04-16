<?php
class AuthController {
    public static function login() { require __DIR__ . '/../views/login.php'; }
    public static function register() { require __DIR__ . '/../views/register.php'; }
    public static function logout() { require __DIR__ . '/../logout.php'; }
}
