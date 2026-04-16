<?php
require_once __DIR__ . '/../models/Event.php';

class EventController
{
    public static function home($pdo)
    {
        $eventModel = new Event($pdo);

        $currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($currentPage < 1) {
            $currentPage = 1;
        }

        $limit = 12;
        $offset = ($currentPage - 1) * $limit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $date = isset($_GET['date']) ? trim($_GET['date']) : null;
        $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'date_asc';

        if ($search === '') {
            $search = null;
        }
        if ($date === '') {
            $date = null;
        }

        $events = $eventModel->getAll($limit, $offset, $search, $date, $sort);
        $total_rows = $eventModel->getCount($search, $date);
        $total_pages = (int) ceil($total_rows / $limit);

        $page = $currentPage;
        $sortValue = $sort;

        require __DIR__ . '/../templates/event_list.php';
    }

    public static function details($pdo)
    {
        require __DIR__ . '/../views/event_details.php';
    }
}
