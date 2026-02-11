<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/src/Controllers/AuthController.php';
require_once __DIR__ . '/src/Controllers/EventController.php';

$page = $_GET['page'] ?? 'home';
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

switch ($page) {

    case 'login':
        (new AuthController())->login();
        break;

    case 'register':
        (new AuthController())->register();
        break;

    case 'logout':
        session_destroy();
        header('Location: ?page=home');
        exit;

    case 'profile':
        if (!$user_id) {
            header('Location: ?page=login');
            exit;
        }
        require_once __DIR__ . '/templates/profile.php';
        break;

    case 'add_event':
        if (!$user_id || $user_role !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            exit;
        }
        (new EventController())->add();
        break;

    case 'events':
        (new EventController())->index();
        break;

    case 'edit_event':
        if (!$user_id || $user_role !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            exit;
        }
        (new EventController())->edit();
        break;

    case 'delete_event':
        if (!$user_id || $user_role !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            exit;
        }
        (new EventController())->delete();
        break;

    case 'upload_poster':
        if (!$user_id || $user_role !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            exit;
        }
        (new EventController())->uploadPoster();
        break;

    case 'home':
    default:
        require_once __DIR__ . '/templates/home_new.php';
        break;
}
