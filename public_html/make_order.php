<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id  = (int)$_SESSION['user_id'];
$event_id = (int)($_GET['id'] ?? 0);

if ($event_id <= 0) {
    die("Мероприятие не найдено.");
}

// Получаем мероприятие
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    die("Мероприятие не найдено.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF
    if (
        empty($_SESSION['csrf_token']) ||
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Ошибка безопасности (CSRF)");
    }

    $qty = (int)($_POST['quantity'] ?? 0);

    if ($qty <= 0) {
        $error = "Введите корректное количество билетов.";
    } else {

        try {
            $pdo->beginTransaction();

            // Блокируем строку мероприятия
            $stmt = $pdo->prepare("
                SELECT available_tickets 
                FROM products 
                WHERE id = ? 
                FOR UPDATE
            ");
            $stmt->execute([$event_id]);
            $row = $stmt->fetch();

            if (!$row) {
                throw new Exception("Мероприятие не найдено.");
            }

            if ($row['available_tickets'] < $qty) {
                throw new Exception(
                    "Недостаточно билетов. Осталось: {$row['available_tickets']}"
                );
            }

            // Уменьшаем количество билетов
            $stmt = $pdo->prepare("
                UPDATE products 
                SET available_tickets = available_tickets - ? 
                WHERE id = ?
            ");
            $stmt->execute([$qty, $event_id]);

            // Создаем заказ
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, product_id, quantity, status, created_at)
                VALUES (?, ?, ?, 'new', NOW())
            ");
            $stmt->execute([$user_id, $event_id, $qty]);

            $pdo->commit();

            header("Location: profile.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Покупка билетов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4" style="max-width:600px;">
    <a href="index.php" class="btn btn-outline-secondary btn-sm mb-3">
        ← Назад к афише
    </a>

    <div class="card shadow-sm">
        <div class="card-body">
            <h4><?= htmlspecialchars($event['title']) ?></h4>

            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

            <p class="fw-bold">
                Цена билета: <?= htmlspecialchars($event['price']) ?> ₽
            </p>

            <p class="text-muted">
                Осталось билетов: <?= (int)$event['available_tickets'] ?>
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($event['available_tickets'] > 0): ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Количество билетов</label>
                        <input
                            type="number"
                            name="quantity"
                            class="form-control"
                            min="1"
                            max="<?= (int)$event['available_tickets'] ?>"
                            value="1"
                            required
                        >
                    </div>

                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <button class="btn btn-primary w-100">
                        Купить
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    Билеты закончились
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
