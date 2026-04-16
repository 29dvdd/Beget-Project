<?php
require_once __DIR__ . '/../config/db.php';

class OrderController
{
    private static function ensureSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private static function requireLogin()
    {
        self::ensureSession();

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Сначала войдите в систему.';
            header('Location: index.php?page=login');
            exit;
        }
    }

    private static function checkCsrf()
    {
        self::ensureSession();

        if (
            empty($_SESSION['csrf_token']) ||
            empty($_POST['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            http_response_code(403);
            die('Ошибка безопасности: неверный CSRF-токен');
        }
    }

    public static function profile()
    {
        global $pdo;

        self::requireLogin();

        $userId = (int)$_SESSION['user_id'];

        $flashSuccess = isset($_SESSION['flash_success']) ? $_SESSION['flash_success'] : '';
        $flashError   = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : '';

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $sql = "
            SELECT 
                o.id,
                o.user_id,
                o.event_id,
                o.quantity,
                o.qty,
                o.price_at_purchase,
                o.total,
                o.status,
                o.created_at,
                e.title,
                e.description,
                e.event_date,
                e.venue,
                e.poster_url,
                e.price
            FROM orders o
            INNER JOIN events e ON e.id = o.event_id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC, o.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($userId));
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/profile.php';
    }

    public static function buy()
    {
        global $pdo;

        self::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Неверный метод запроса');
        }

        self::checkCsrf();

        $userId  = (int)$_SESSION['user_id'];
        $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
        $qty     = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

        if ($eventId <= 0) {
            $_SESSION['flash_error'] = 'Некорректное мероприятие.';
            header('Location: index.php?page=home');
            exit;
        }

        if ($qty <= 0) {
            $_SESSION['flash_error'] = 'Некорректное количество билетов.';
            header('Location: index.php?page=event_details&id=' . $eventId);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? FOR UPDATE");
            $stmt->execute(array($eventId));
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                throw new Exception('Мероприятие не найдено.');
            }

            $available = isset($event['available_tickets']) ? (int)$event['available_tickets'] : 0;
            $price     = isset($event['price']) ? (float)$event['price'] : 0;

            if ($available < $qty) {
                throw new Exception('Недостаточно билетов. Осталось: ' . $available);
            }

            $newAvailable = $available - $qty;

            $upd = $pdo->prepare("UPDATE events SET available_tickets = ? WHERE id = ?");
            $upd->execute(array($newAvailable, $eventId));

            $total = $price * $qty;

            $ins = $pdo->prepare("
                INSERT INTO orders (user_id, event_id, quantity, qty, price_at_purchase, total, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())
            ");
            $ins->execute(array(
                $userId,
                $eventId,
                $qty,
                $qty,
                $price,
                $total
            ));

            $pdo->commit();

            $_SESSION['flash_success'] = 'Билеты успешно куплены.';
            header('Location: index.php?page=profile');
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: index.php?page=event_details&id=' . $eventId);
            exit;
        }
    }

    public static function cancel()
    {
        global $pdo;

        self::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Неверный метод запроса');
        }

        self::checkCsrf();

        $userId  = (int)$_SESSION['user_id'];
        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;

        if ($orderId <= 0) {
            $_SESSION['flash_error'] = 'Некорректный заказ.';
            header('Location: index.php?page=profile');
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT * 
                FROM orders 
                WHERE id = ? AND user_id = ?
                FOR UPDATE
            ");
            $stmt->execute(array($orderId, $userId));
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                throw new Exception('Заказ не найден.');
            }

            $currentStatus = isset($order['status']) ? $order['status'] : '';

            if ($currentStatus === 'cancelled' || $currentStatus === 'canceled') {
                throw new Exception('Этот заказ уже отменён.');
            }

            $eventId = isset($order['event_id']) ? (int)$order['event_id'] : 0;
            $qty = 1;

            if (isset($order['quantity']) && (int)$order['quantity'] > 0) {
                $qty = (int)$order['quantity'];
            } elseif (isset($order['qty']) && (int)$order['qty'] > 0) {
                $qty = (int)$order['qty'];
            }

            $updOrder = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
            $updOrder->execute(array($orderId, $userId));

            if ($eventId > 0) {
                $updEvent = $pdo->prepare("
                    UPDATE events
                    SET available_tickets = available_tickets + ?
                    WHERE id = ?
                ");
                $updEvent->execute(array($qty, $eventId));
            }

            $pdo->commit();

            $_SESSION['flash_success'] = 'Заказ успешно отменён.';
            header('Location: index.php?page=profile');
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $_SESSION['flash_error'] = $e->getMessage();
            header('Location: index.php?page=profile');
            exit;
        }
    }
}