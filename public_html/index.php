<?php
/**
 * РОУТЕР - Единая точка входа
 * Проект: Афиша мероприятий
 * Студент: Барабашов Давид
 * Группа: 9-ИС202
 */

session_start();

// Подключаем конфигурацию БД
require_once __DIR__ . '/config/db.php';

// Подключаем модели
require_once __DIR__ . '/src/Models/Event.php';

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Определяем страницу
$page = $_GET['page'] ?? 'home';

// Роутинг
switch ($page) {

    case 'home':
        // Главная страница - список мероприятий
        $eventModel = new Event($pdo);

        // Пагинация
        $currentPage = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($currentPage < 1) $currentPage = 1;

        $limit = 12;
        $offset = ($currentPage - 1) * $limit;

        // Фильтры
        $search = $_GET['search'] ?? null;
        $date   = $_GET['date'] ?? null;

        // Получаем данные
        $events = $eventModel->getAll($limit, $offset, $search, $date);
        $total_rows  = $eventModel->getCount($search, $date);
        $total_pages = (int)ceil($total_rows / $limit);

        // переменные для шаблона
        $page = $currentPage;

        require __DIR__ . '/templates/event_list.php';
        break;

    case 'add_event':
        // Добавление мероприятия (только админ)
        require __DIR__ . '/check_admin.php';
        require __DIR__ . '/add_item.php';
        break;

    case 'edit_event':
        // Редактирование мероприятия (только админ)
        require __DIR__ . '/check_admin.php';
        require __DIR__ . '/edit_item.php';
        break;

    case 'update_event':
        // Обновление мероприятия (только админ)
        require __DIR__ . '/check_admin.php';
        require __DIR__ . '/update_product.php';
        break;

    case 'delete_event':
        // Удаление мероприятия (только админ)
        require __DIR__ . '/check_admin.php';
        require __DIR__ . '/delete_event.php';
        break;

    case 'event_details':
        // Детальная страница мероприятия
        require __DIR__ . '/event_details.php';
        break;

    case 'buy_tickets':
        // Покупка билетов (только авторизованный пользователь)
        require __DIR__ . '/buy_tickets.php';
        break;

    case 'profile':
        // Мои билеты
        require __DIR__ . '/profile.php';
        break;

    case 'cancel_ticket':
        // Отмена билета (пользователь/админ)
        require __DIR__ . '/cancel_ticket.php';
        break;

    case 'admin_orders':
        // Админ-панель заказов (все билеты всех пользователей)
        require __DIR__ . '/check_admin.php';
        require __DIR__ . '/admin_orders.php';
        break;

    case 'login':
        require __DIR__ . '/login.php';
        break;

    case 'register':
        require __DIR__ . '/register.php';
        break;

    case 'logout':
        require __DIR__ . '/logout.php';
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Страница не найдена</h1>";
        echo "<a href='index.php?page=home'>На главную</a>";
        break;
}