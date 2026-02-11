<?php

class Event {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Создание мероприятия
    public function create(string $title, string $description, string $event_date, string $venue, ?string $poster_url = null): bool {
        $sql = "INSERT INTO events (title, description, event_date, venue, poster_url) 
                VALUES (:title, :description, :event_date, :venue, :poster_url)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':event_date' => $event_date,
            ':venue' => $venue,
            ':poster_url' => $poster_url
        ]);
    }

    // Получение всех мероприятий с фильтрацией по дате
    public function getAll(?string $date_filter = null): array {
        $sql = "SELECT * FROM events";
        $params = [];
        
        if ($date_filter) {
            $sql .= " WHERE event_date = :event_date";
            $params[':event_date'] = $date_filter;
        }
        
        $sql .= " ORDER BY event_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    // Получение мероприятия по ID
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // Обновление мероприятия
    public function update(int $id, string $title, string $description, string $event_date, string $venue, ?string $poster_url = null): bool {
        $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, venue = ?, poster_url = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$title, $description, $event_date, $venue, $poster_url, $id]);
    }

    // Удаление мероприятия
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Получение уникальных дат мероприятий для фильтра
    public function getUniqueDates(): array {
        $stmt = $this->db->query("SELECT DISTINCT event_date FROM events ORDER BY event_date ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
