<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/EventController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/AdminController.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';

switch ($page) {
    case 'home':
        EventController::home($pdo);
        break;

    case 'event_details':
        EventController::details($pdo);
        break;

    case 'login':
        AuthController::login();
        break;

    case 'register':
        AuthController::register();
        break;

    case 'logout':
        AuthController::logout();
        break;

    case 'profile':
        OrderController::profile();
        break;

    case 'buy_tickets':
        OrderController::buy();
        break;

    case 'cancel_ticket':
        OrderController::cancel();
        break;

    case 'add_event':
        AdminController::add();
        break;

    case 'edit_event':
        AdminController::edit();
        break;

    case 'update_event':
        AdminController::update();
        break;

    case 'delete_event':
        AdminController::delete();
        break;

    case 'admin_orders':
        AdminController::orders();
        break;

    case 'update_order_status':
        AdminController::updateOrderStatus();
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/templates/404.php';
        break;
}
