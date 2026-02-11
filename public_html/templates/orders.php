<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заказы</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h1>Мои заказы</h1>

    <?php if (empty($orders)): ?>
        <p>Вы ещё не сделали ни одного заказа.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Мероприятие</th>
                        <th>Дата</th>
                        <th>Цена</th>
                        <th>Количество</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= (int)$order['id'] ?></td>
                            <td><?= htmlspecialchars($order['title']) ?></td>
                            <td><?= htmlspecialchars($order['event_date']) ?></td>
                            <td><?= htmlspecialchars($order['price']) ?> ₽</td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="?page=home" class="btn btn-secondary mt-3">На главную</a>
</div>

</body>
</html>
