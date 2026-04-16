<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Event.php';

class AdminController
{
    private static function ensureSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private static function requireAdmin()
    {
        self::ensureSession();

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['flash_error'] = 'Доступ запрещён.';
            header('Location: index.php?page=home');
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

    private static function uploadPoster($fieldName, $currentPoster = '')
    {
        if (empty($_FILES[$fieldName]) || !isset($_FILES[$fieldName]['error'])) {
            return $currentPoster;
        }

        if ($_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentPoster;
        }

        if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return $currentPoster;
        }

        if (!is_dir(__DIR__ . '/../uploads')) {
            @mkdir(__DIR__ . '/../uploads', 0755, true);
        }

        $tmpName = $_FILES[$fieldName]['tmp_name'];
        $originalName = $_FILES[$fieldName]['name'];
        $size = (int)$_FILES[$fieldName]['size'];

        if ($size <= 0 || $size > 5 * 1024 * 1024) {
            return $currentPoster;
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');

        if (!in_array($ext, $allowed)) {
            return $currentPoster;
        }

        $newName = 'poster_' . uniqid() . '.' . $ext;
        $targetAbsolute = __DIR__ . '/../uploads/' . $newName;
        $targetRelative = 'uploads/' . $newName;

        if (move_uploaded_file($tmpName, $targetAbsolute)) {
            return $targetRelative;
        }

        return $currentPoster;
    }

    public static function add()
    {
        global $pdo;

        self::requireAdmin();

        $flashSuccess = isset($_SESSION['flash_success']) ? $_SESSION['flash_success'] : '';
        $flashError   = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : '';

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::checkCsrf();

            $title = isset($_POST['title']) ? trim($_POST['title']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $eventDate = isset($_POST['event_date']) ? trim($_POST['event_date']) : '';
            $venue = isset($_POST['venue']) ? trim($_POST['venue']) : '';
            $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
            $availableTickets = isset($_POST['available_tickets']) ? (int)$_POST['available_tickets'] : 0;

            if ($title === '' || $eventDate === '') {
                $_SESSION['flash_error'] = 'Заполните обязательные поля.';
                header('Location: index.php?page=add_event');
                exit;
            }

            $posterUrl = self::uploadPoster('poster', '');

            $eventModel = new Event($pdo);
            $ok = $eventModel->create(array(
                'title' => $title,
                'description' => $description,
                'event_date' => $eventDate,
                'venue' => $venue,
                'price' => $price,
                'available_tickets' => $availableTickets,
                'poster_url' => $posterUrl
            ));

            if ($ok) {
                $_SESSION['flash_success'] = 'Мероприятие успешно добавлено.';
                header('Location: index.php?page=home');
                exit;
            }

            $_SESSION['flash_error'] = 'Не удалось добавить мероприятие.';
            header('Location: index.php?page=add_event');
            exit;
        }

        $isCreate = true;
        $event = array(
            'id' => 0,
            'title' => '',
            'description' => '',
            'event_date' => '',
            'venue' => '',
            'price' => '',
            'available_tickets' => '',
            'poster_url' => ''
        );

        require __DIR__ . '/../views/edit_item.php';
    }

    public static function edit()
    {
        global $pdo;

        self::requireAdmin();

        $eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($eventId <= 0) {
            $_SESSION['flash_error'] = 'Некорректный ID мероприятия.';
            header('Location: index.php?page=home');
            exit;
        }

        $eventModel = new Event($pdo);
        $event = $eventModel->getById($eventId);

        if (!$event) {
            $_SESSION['flash_error'] = 'Мероприятие не найдено.';
            header('Location: index.php?page=home');
            exit;
        }

        $flashSuccess = isset($_SESSION['flash_success']) ? $_SESSION['flash_success'] : '';
        $flashError   = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : '';

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $isCreate = false;

        require __DIR__ . '/../views/edit_item.php';
    }

    public static function update()
    {
        global $pdo;

        self::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Неверный метод запроса');
        }

        self::checkCsrf();

        $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

        if ($eventId <= 0) {
            $_SESSION['flash_error'] = 'Некорректный ID мероприятия.';
            header('Location: index.php?page=home');
            exit;
        }

        $eventModel = new Event($pdo);
        $oldEvent = $eventModel->getById($eventId);

        if (!$oldEvent) {
            $_SESSION['flash_error'] = 'Мероприятие не найдено.';
            header('Location: index.php?page=home');
            exit;
        }

        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $eventDate = isset($_POST['event_date']) ? trim($_POST['event_date']) : '';
        $venue = isset($_POST['venue']) ? trim($_POST['venue']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $availableTickets = isset($_POST['available_tickets']) ? (int)$_POST['available_tickets'] : 0;

        if ($title === '' || $eventDate === '') {
            $_SESSION['flash_error'] = 'Заполните обязательные поля.';
            header('Location: index.php?page=edit_event&id=' . $eventId);
            exit;
        }

        $posterUrl = isset($oldEvent['poster_url']) ? $oldEvent['poster_url'] : '';
        $posterUrl = self::uploadPoster('poster', $posterUrl);

        $ok = $eventModel->update($eventId, array(
            'title' => $title,
            'description' => $description,
            'event_date' => $eventDate,
            'venue' => $venue,
            'price' => $price,
            'available_tickets' => $availableTickets,
            'poster_url' => $posterUrl
        ));

        if ($ok) {
            $_SESSION['flash_success'] = 'Мероприятие успешно обновлено.';
            header('Location: index.php?page=event_details&id=' . $eventId);
            exit;
        }

        $_SESSION['flash_error'] = 'Не удалось обновить мероприятие.';
        header('Location: index.php?page=edit_event&id=' . $eventId);
        exit;
    }

    public static function delete()
    {
        global $pdo;

        self::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Неверный метод запроса');
        }

        self::checkCsrf();

        $eventId = 0;

        if (isset($_POST['event_id'])) {
            $eventId = (int)$_POST['event_id'];
        } elseif (isset($_POST['id'])) {
            $eventId = (int)$_POST['id'];
        }

        if ($eventId <= 0) {
            $_SESSION['flash_error'] = 'Некорректный ID мероприятия.';
            header('Location: index.php?page=home');
            exit;
        }

        $eventModel = new Event($pdo);
        $ok = $eventModel->delete($eventId);

        if ($ok) {
            $_SESSION['flash_success'] = 'Мероприятие удалено.';
        } else {
            $_SESSION['flash_error'] = 'Не удалось удалить мероприятие.';
        }

        header('Location: index.php?page=home');
        exit;
    }

    public static function orders()
    {
        global $pdo;

        self::requireAdmin();

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
                u.email,
                e.title,
                e.event_date
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            LEFT JOIN events e ON e.id = o.event_id
            ORDER BY o.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Все заказы';
        require __DIR__ . '/../templates/header.php';
        ?>

        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">Все заказы</h1>
                <a href="index.php?page=home" class="btn btn-outline-secondary">На главную</a>
            </div>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($flashError); ?></div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="alert alert-info">Заказов пока нет.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Пользователь</th>
                                <th>Мероприятие</th>
                                <th>Дата</th>
                                <th>Кол-во</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Куплено</th>
                                <th>Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $qty = 1;
                            if (isset($order['quantity']) && (int)$order['quantity'] > 0) {
                                $qty = (int)$order['quantity'];
                            } elseif (isset($order['qty']) && (int)$order['qty'] > 0) {
                                $qty = (int)$order['qty'];
                            }

                            $sum = 0;
                            if (isset($order['total']) && (float)$order['total'] > 0) {
                                $sum = (float)$order['total'];
                            } elseif (isset($order['price_at_purchase']) && (float)$order['price_at_purchase'] > 0) {
                                $sum = (float)$order['price_at_purchase'] * $qty;
                            }
                            ?>
                            <tr>
                                <td><?php echo (int)$order['id']; ?></td>
                                <td><?php echo htmlspecialchars(isset($order['email']) ? $order['email'] : ''); ?></td>
                                <td>
                                    <?php if (!empty($order['event_id'])): ?>
                                        <a href="index.php?page=event_details&id=<?php echo (int)$order['event_id']; ?>">
                                            <?php echo htmlspecialchars(isset($order['title']) ? $order['title'] : ''); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars(isset($order['title']) ? $order['title'] : ''); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(isset($order['event_date']) ? $order['event_date'] : ''); ?></td>
                                <td><?php echo $qty; ?></td>
                                <td><?php echo number_format($sum, 2, '.', ' '); ?> ₽</td>
                                <td><?php echo htmlspecialchars(isset($order['status']) ? $order['status'] : ''); ?></td>
                                <td><?php echo htmlspecialchars(isset($order['created_at']) ? $order['created_at'] : ''); ?></td>
                                <td>
                                    <form method="POST" action="index.php?page=update_order_status" class="d-flex gap-2">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">

                                        <select name="status" class="form-select form-select-sm">
                                            <?php
                                            $statuses = array('new', 'paid', 'cancelled');
                                            foreach ($statuses as $status) {
                                                $selected = (isset($order['status']) && $order['status'] === $status) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($status) . '" ' . $selected . '>' . htmlspecialchars($status) . '</option>';
                                            }
                                            ?>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary">Сохранить</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <?php
        require __DIR__ . '/../templates/footer.php';
    }

    public static function updateOrderStatus()
    {
        global $pdo;

        self::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Неверный метод запроса');
        }

        self::checkCsrf();

        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        $status  = isset($_POST['status']) ? trim($_POST['status']) : '';

        $allowed = array('new', 'paid', 'cancelled');

        if ($orderId <= 0 || !in_array($status, $allowed)) {
            $_SESSION['flash_error'] = 'Некорректные данные для обновления заказа.';
            header('Location: index.php?page=admin_orders');
            exit;
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $ok = $stmt->execute(array($status, $orderId));

        if ($ok) {
            $_SESSION['flash_success'] = 'Статус заказа обновлён.';
        } else {
            $_SESSION['flash_error'] = 'Не удалось обновить статус заказа.';
        }

        header('Location: index.php?page=admin_orders');
        exit;
    }
}