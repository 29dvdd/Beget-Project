<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Покупка билета</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4" style="max-width:600px;">

    <a href="?page=products" class="btn btn-outline-secondary mb-3">&larr; Назад к мероприятиям</a>

    <div class="card shadow-sm">
        <div class="card-body">
            <h4><?= htmlspecialchars($product['title']) ?></h4>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <p><strong>Цена:</strong> <?= htmlspecialchars($product['price']) ?> ₽</p>
            <p><strong>Осталось билетов:</strong> <?= htmlspecialchars($product['available_tickets']) ?></p>

            <?php if (!empty($message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($product['available_tickets'] > 0): ?>
                <form method="post">
                    <div class="mb-3">
                        <label>Количество билетов</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Купить</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">Билеты закончились</div>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
