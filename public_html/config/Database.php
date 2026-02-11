<?php
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = 'localhost';
            $db   = 'b9628214_test';
            $user = 'b9628214_test';
            $pass = 'Parol123';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Выводим детальную ошибку для отладки
                die("Ошибка подключения к БД: " . $e->getMessage() . "<br>Проверьте данные: $db, $user");
            }
        }

        return self::$instance;
    }
}
