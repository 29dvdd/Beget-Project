<?php
require_once __DIR__ . '/../../config/Database.php';

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(
        string $title,
        string $description,
        float $price,
        int $tickets,
        ?string $image,
        string $eventDate,
        ?string $address,
        ?float $latitude,
        ?float $longitude
    ): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO products
            (title, description, price, available_tickets, image_url, event_date, address, latitude, longitude, is_published)
            VALUES
            (:title, :description, :price, :tickets, :image, :event_date, :address, :latitude, :longitude, 1)"
        );

        return $stmt->execute([
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'tickets' => $tickets,
            'image' => $image,
            'event_date' => $eventDate,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM products WHERE is_published = 1 ORDER BY event_date ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id AND is_published = 1");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    }
}
