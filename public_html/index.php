<?php
session_start();
require 'db.php';

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Количество мероприятий на странице
$offset = ($page - 1) * $limit;

// Построение запроса
$sql = "SELECT * FROM products";
$params = [];
$where = [];

// Фильтр по опубликованным (soft delete)
$where[] = "is_published = 1";

// Поиск по названию
if (!empty($_GET['search'])) {
    $where[] = "title LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
}

// Фильтр по дате
if (!empty($_GET['date'])) {
    $where[] = "DATE(event_date) = ?";
    $params[] = $_GET['date'];
}

// Объединение условий
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Получаем общее количество для пагинации
$count_sql = "SELECT COUNT(*) FROM products" . ($where ? " WHERE " . implode(" AND ", $where) : "");
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Сортировка и лимит
$sql .= " ORDER BY event_date ASC, id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Афиша мероприятий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-light bg-light px-4 mb-4 shadow-sm">
    <span class="navbar-brand">Афиша</span>
    <div class="d-flex align-items-center gap-2">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Привет, <b><?= htmlspecialchars($_SESSION['email']) ?></b></span>
            <a href="profile.php" class="btn btn-outline-primary btn-sm">Мой профиль</a>

            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="add_item.php" class="btn btn-success btn-sm">+ Добавить мероприятие</a>
            <?php endif; ?>

            <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-sm">Войти</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">

    <!-- Поиск и фильтры -->
    <div class="card mb-4 p-3 bg-light">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Поиск по названию</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Введите название мероприятия" 
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Фильтр по дате</label>
                <input type="date" name="date" class="form-control" 
                       value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">Найти</button>
            </div>
        </form>
        <?php if (!empty($_GET['search']) || !empty($_GET['date'])): ?>
            <div class="mt-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">Сбросить фильтры</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php if ($products): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php
                            $img = $product['image_url'] ?: 'https://via.placeholder.com/300x200?text=Афиша';
                        ?>
                        <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" style="height:200px;object-fit:cover;">

                        <div class="card-body">
                            <h5><?= htmlspecialchars($product['title']) ?></h5>
                            <p class="text-truncate"><?= htmlspecialchars($product['description']) ?></p>
                            <p class="fw-bold"><?= number_format((float)$product['price'], 2, '.', ' ') ?> ₽</p>
                            <p class="text-muted">Билетов: <?= (int)$product['available_tickets'] ?></p>
                            <p class="text-muted">Дата: <?= htmlspecialchars(date('d.m.Y', strtotime($product['event_date']))) ?></p>
                            
                            <?php if (!empty($product['address'])): ?>
                                <p class="text-muted small">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($product['address']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['address']) && !empty($product['latitude']) && !empty($product['longitude'])): ?>
                                <div class="mt-2">
                                    <img src="https://static-maps.yandex.ru/1.x/?lang=ru_RU&ll=<?= htmlspecialchars($product['longitude']) ?>,<?= htmlspecialchars($product['latitude']) ?>&z=15&l=map&size=300,200" 
                                         alt="Карта" class="img-fluid rounded" style="max-height: 150px; width: 100%; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer bg-white">
                            <a href="make_order.php?id=<?= (int)$product['id'] ?>" class="btn btn-primary w-100 mb-2">Купить билет</a>

                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <div class="d-flex gap-2">
                                    <a href="edit_item.php?id=<?= (int)$product['id'] ?>" class="btn btn-warning btn-sm flex-fill">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="post" action="delete_product.php" onsubmit="return confirm('Снять мероприятие с публикации?');" class="flex-fill">
                                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <button class="btn btn-danger btn-sm w-100" title="Снять с публикации">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <p class="mb-0">Мероприятий не найдено.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Пагинация -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Навигация по страницам" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['date']) ? '&date=' . urlencode($_GET['date']) : '' ?>">Назад</a>
                    </li>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['date']) ? '&date=' . urlencode($_GET['date']) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['date']) ? '&date=' . urlencode($_GET['date']) : '' ?>">Вперёд</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="text-center text-muted mb-4">
            Страница <?= $page ?> из <?= $total_pages ?> (всего мероприятий: <?= $total_rows ?>)
        </div>
    <?php endif; ?>
</div>

</body>
</html>
