<?php
require_once __DIR__ . '/../Models/Event.php';

class EventController {
    
    public function index() {
        $date_filter = $_GET['event_date'] ?? null;
        $events = (new Event())->getAll($date_filter);
        $unique_dates = (new Event())->getUniqueDates();
        
        require_once __DIR__ . '/../../templates/events.php';
    }
    
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $event_date = $_POST['event_date'] ?? '';
            $venue = trim($_POST['venue'] ?? '');
            
            if (empty($title) || empty($event_date)) {
                $error = "Заполните название и дату мероприятия!";
                require_once __DIR__ . '/../../templates/add_event.php';
                return;
            }
            
            $event = new Event();
            if ($event->create($title, $description, $event_date, $venue)) {
                $success = "Мероприятие успешно добавлено!";
                header('Location: ?page=events&success=1');
                exit;
            } else {
                $error = "Ошибка при добавлении мероприятия!";
            }
        }
        
        require_once __DIR__ . '/../../templates/add_event.php';
    }
    
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $event = (new Event())->getById($id);
        
        if (!$event) {
            die("Мероприятие не найдено");
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $event_date = $_POST['event_date'] ?? '';
            $venue = trim($_POST['venue'] ?? '');
            
            if (empty($title) || empty($event_date)) {
                $error = "Заполните название и дату мероприятия!";
            } else {
                $eventModel = new Event();
                if ($eventModel->update($id, $title, $description, $event_date, $venue, $event['poster_url'])) {
                    $success = "Мероприятие успешно обновлено!";
                    header('Location: ?page=events&success=1');
                    exit;
                } else {
                    $error = "Ошибка при обновлении мероприятия!";
                }
            }
        }
        
        require_once __DIR__ . '/../../templates/edit_event.php';
    }
    
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                die("CSRF Attack blocked");
            }
            
            $event = new Event();
            if ($event->delete($id)) {
                header('Location: ?page=events&success=1');
            } else {
                die("Ошибка при удалении мероприятия");
            }
        }
    }
    
    public function uploadPoster() {
        // Этот метод не нужен, так как загрузка идет через отдельный файл upload_poster.php
        header('Location: ?page=events');
        exit;
    }
}
