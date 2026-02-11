<?php
require_once __DIR__ . '/../../config/Database.php';

class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Создание нового заказа
     */
    public function create(int $userId, int $productId, int $quantity): bool
    {
        try {
            $this->db->beginTransaction();

            // Блокировка мероприятия
            $stmt = $this->db->prepare("SELECT available_tickets FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Мероприятие не найдено");
            }

            if ($product['available_tickets'] < $quantity) {
                throw new Exception("Недостаточно билетов. Осталось: {$product['available_tickets']}");
            }

            // Обновляем количество билетов
            $stmt = $this->db->prepare("UPDATE products SET available_tickets = available_tickets - ? WHERE id = ?");
            $stmt->execute([$quantity, $productId]);

            // Создаем заказ
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, product_id, quantity, status, created_at) VALUES (?, ?, ?, 'new', NOW())");
            $stmt->execute([$userId, $productId, $quantity]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Получить все заказы пользователя
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.id as order_id,
                o.quantity,
                o.status,
                o.created_at,
                p.title,
                p.price,
                p.image_url
            FROM orders o
            JOIN products p ON o.product_id = p.id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
