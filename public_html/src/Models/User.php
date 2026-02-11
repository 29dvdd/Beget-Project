<?php

require_once __DIR__ . '/../../config/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, email, password_hash, role FROM users WHERE email = :email"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(string $email, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (email, password_hash, role)
             VALUES (:email, :hash, 'client')"
        );

        return $stmt->execute([
            'email' => $email,
            'hash'  => $hash
        ]);
    }
}
