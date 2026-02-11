<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Мероприятия</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-4">

    <h1>Мероприятия</h1>

    <?php if (empty($products)): ?>
        <p>Мероприятий пока нет.</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($products as $product): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if ($product['image_url']): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="Афиша">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                            <p><strong>Цена:</strong> <?= htmlspecialchars($product['price']) ?> ₽</p>
                            <p><strong>Доступно билетов:</strong> <?= htmlspecialchars($product['available_tickets']) ?></p>
                            <p><strong>Дата:</strong> <?= htmlspecialchars($product['event_date']) ?></p>
                            <a href="?page=buy&id=<?= (int)$product['id'] ?>" class="btn btn-primary">Купить билет</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-3">
        <a href="?page=home" class="btn btn-secondary">Назад</a>
    </div>

</div>
</body>
</html>
