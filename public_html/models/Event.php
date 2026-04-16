<?php
/**
 * Модель Event - работа с таблицей events
 * Проект: Афиша мероприятий
 * Студент: Барабашов Давид
 */

class Event
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Получить все мероприятия с пагинацией
     */
    public function getAll($limit = 12, $offset = 0, $search = null, $date = null, $sort = 'date_asc')
    {
        $sql = "SELECT * FROM events";
        $params = array();
        $where = array();

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

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sortMap = array(
            'date_asc'   => 'event_date ASC, id DESC',
            'date_desc'  => 'event_date DESC, id DESC',
            'price_asc'  => 'price ASC, event_date ASC, id DESC',
            'price_desc' => 'price DESC, event_date ASC, id DESC',
        );

        $orderBy = isset($sortMap[$sort]) ? $sortMap[$sort] : $sortMap['date_asc'];
        $sql .= " ORDER BY " . $orderBy . " LIMIT ?, ?";

        $stmt = $this->db->prepare($sql);

        // все параметры делаем позиционными
        foreach ($params as $value) {
            $stmt->bindValue(count($params) ? array_search($value, $params) + 1 : 1, $value);
        }

        // лучше без array_search — надёжно по индексу
        $index = 1;
        foreach ($params as $value) {
            $stmt->bindValue($index, $value, PDO::PARAM_STR);
            $index++;
        }

        $stmt->bindValue($index, (int)$offset, PDO::PARAM_INT);
        $index++;
        $stmt->bindValue($index, (int)$limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получить общее количество мероприятий
     */
    public function getCount($search = null, $date = null)
    {
        $sql = "SELECT COUNT(*) FROM events";
        $params = array();
        $where = array();

        if (!empty($search)) {
            $where[] = "title LIKE ?";
            $params[] = '%' . $search . '%';
        }

        if (!empty($date)) {
            $where[] = "DATE(event_date) = ?";
            $params[] = $date;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Получить мероприятие по ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute(array((int)$id));

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

    /**
     * Создать новое мероприятие
     */
    public function create($data)
    {
        $sql = "INSERT INTO events (title, description, event_date, venue, price, available_tickets, poster_url)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(array(
            isset($data['title']) ? $data['title'] : '',
            isset($data['description']) ? $data['description'] : '',
            isset($data['event_date']) ? $data['event_date'] : '',
            isset($data['venue']) ? $data['venue'] : '',
            isset($data['price']) ? $data['price'] : 0,
            isset($data['available_tickets']) ? $data['available_tickets'] : 0,
            isset($data['poster_url']) ? $data['poster_url'] : null
        ));
    }

    /**
     * Обновить мероприятие
     */
    public function update($id, $data)
    {
        $sql = "UPDATE events
                SET title = ?, description = ?, event_date = ?, venue = ?, price = ?, available_tickets = ?, poster_url = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(array(
            isset($data['title']) ? $data['title'] : '',
            isset($data['description']) ? $data['description'] : '',
            isset($data['event_date']) ? $data['event_date'] : '',
            isset($data['venue']) ? $data['venue'] : '',
            isset($data['price']) ? $data['price'] : 0,
            isset($data['available_tickets']) ? $data['available_tickets'] : 0,
            isset($data['poster_url']) ? $data['poster_url'] : null,
            (int)$id
        ));
    }

    /**
     * Удалить мероприятие
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ? LIMIT 1");
        return $stmt->execute(array((int)$id));
    }
}