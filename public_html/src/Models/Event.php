<?php
/**
 * Модель Event - работа с таблицей events
 * Проект: Афиша мероприятий
 * Студент: Барабашов Давид
 */

class Event {
    private PDO $db;
    
    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }
    
    /**
     * Получить все мероприятия с пагинацией
     */
    public function getAll(int $limit = 12, int $offset = 0, ?string $search = null, ?string $date = null): array {
        $sql = "SELECT * FROM events";
        $params = [];
        $where = [];
        
        // Поиск по названию
        if (!empty($search)) {
            $where[] = "title LIKE ?";
            $params[] = '%' . $search . '%';
        }
        
        // Фильтр по дате
        if (!empty($date)) {
            $where[] = "DATE(event_date) = ?";
            $params[] = $date;
        }
        
        // Объединение условий
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY event_date ASC, id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        // Привязываем параметры WHERE
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value);
        }
        
        // Привязываем LIMIT и OFFSET
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Получить общее количество мероприятий
     */
    public function getCount(?string $search = null, ?string $date = null): int {
        $sql = "SELECT COUNT(*) FROM events";
        $params = [];
        $where = [];
        
        if (!empty($search)) {
            $where[] = "title LIKE ?";
            $params[] = '%' . $search . '%';
        }
        
        if (!empty($date)) {
            $where[] = "DATE(event_date) = ?";
            $params[] = $date;
        }
        
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Получить мероприятие по ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Создать новое мероприятие
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO events (title, description, event_date, venue, price, available_tickets, poster_url)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['event_date'],
            $data['venue'] ?? '',
            $data['price'] ?? 0,
            $data['available_tickets'] ?? 0,
            $data['poster_url'] ?? null
        ]);
    }
    
    /**
     * Обновить мероприятие
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE events 
                SET title = ?, description = ?, event_date = ?, venue = ?, price = ?, available_tickets = ?, poster_url = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['event_date'],
            $data['venue'] ?? '',
            $data['price'] ?? 0,
            $data['available_tickets'] ?? 0,
            $data['poster_url'] ?? null,
            $id
        ]);
    }
    
    /**
     * Удалить мероприятие
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ? LIMIT 1");
        return $stmt->execute([$id]);
    }
}
