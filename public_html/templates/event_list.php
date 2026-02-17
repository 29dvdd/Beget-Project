<?php
// templates/event_list.php

require __DIR__ . '/header.php';

$role = $_SESSION['role'] ?? '';
$isAdmin = ($role === 'admin');
$csrf = $_SESSION['csrf_token'] ?? '';

// Параметры фильтра (как в твоём index.php)
$searchValue = $_GET['search'] ?? '';
$dateValue   = $_GET['date'] ?? '';

// Пагинация (как в твоём index.php)
$currentPage = (int)($page ?? 1);
$totalPages  = (int)($total_pages ?? 1);
?>

<div class="container my-4">

    <!-- Поиск/дата -->
    <div class="card mb-4 p-3">
        <form class="row g-3 align-items-end" method="GET" action="index.php">
            <input type="hidden" name="page" value="home">

            <div class="col-md-6">
                <label class="form-label">Поиск по названию</label>
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Введите название..."
                    value="<?= htmlspecialchars($searchValue) ?>"
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Дата мероприятия</label>
                <input
                    type="date"
                    name="date"
                    class="form-control"
                    value="<?= htmlspecialchars($dateValue) ?>"
                >
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-search"></i> Найти
                </button>
            </div>

            <div class="col-12 text-end">
                <a class="text-muted small text-decoration-none" href="index.php?page=home">
                    Сбросить фильтры
                </a>
            </div>
        </form>
    </div>

    <!-- Карточки -->
    <?php if (empty($events)): ?>
        <div class="alert alert-warning">Мероприятий не найдено.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($events as $event): ?>
                <?php
                $id    = (int)($event['id'] ?? 0);
                $title = (string)($event['title'] ?? '');
                $desc  = (string)($event['description'] ?? '');
                $venue = (string)($event['venue'] ?? '');
                $date  = (string)($event['event_date'] ?? '');
                $poster = $event['poster_url'] ?? null;

                $price   = (float)($event['price'] ?? 0);
                $tickets = (int)($event['available_tickets'] ?? 0);

                $shortDesc = (mb_strlen($desc) > 90) ? (mb_substr($desc, 0, 90) . '...') : $desc;
                ?>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">

                        <!-- Постер -->
                        <?php if (!empty($poster)): ?>
                            <img
                                src="<?= htmlspecialchars($poster) ?>"
                                class="card-img-top"
                                style="height: 220px; object-fit: cover;"
                                alt="<?= htmlspecialchars($title) ?>"
                            >
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                 style="height: 220px;">
                                <span class="text-muted small">Нет постера</span>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2"><?= htmlspecialchars($title) ?></h5>

                            <p class="card-text text-muted mb-3">
                                <?= htmlspecialchars($shortDesc) ?>
                            </p>

                            <div class="small text-muted mb-3">
                                <div class="mb-1">
                                    <i class="bi bi-calendar-event"></i>
                                    <?= htmlspecialchars($date) ?>
                                </div>
                                <div>
                                    <i class="bi bi-geo-alt"></i>
                                    <?= htmlspecialchars($venue) ?>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-success">
                                    <?= number_format($price, 2, '.', '') ?> ₽
                                </span>
                                <span class="badge bg-secondary">
                                    Осталось: <?= $tickets ?>
                                </span>
                            </div>

                            <div class="mt-auto">
                                <!-- Подробнее (большая) -->
                                <a href="index.php?page=event_details&id=<?= $id ?>"
                                   class="btn btn-primary w-100 mb-2">
                                    <i class="bi bi-eye"></i> Подробнее
                                </a>

                                <?php if ($isAdmin): ?>
                                    <div class="d-flex gap-2">
                                        <!-- Редактировать -->
                                        <a href="index.php?page=edit_event&id=<?= $id ?>"
                                           class="btn btn-warning btn-sm w-100">
                                            <i class="bi bi-pencil-square"></i>
                                            Редактировать
                                        </a>

                                        <!-- Удалить (POST + CSRF) -->
                                        <form method="POST"
                                              action="index.php?page=delete_event"
                                              class="w-100"
                                              onsubmit="return confirm('Удалить мероприятие?');">
                                            <input type="hidden" name="event_id" value="<?= $id ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                                <i class="bi bi-trash"></i>
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>

                    </div>
                </div>

            <?php endforeach; ?>
        </div>

        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                            <a class="page-link"
                               href="index.php?page=home&p=<?= $i ?>&search=<?= urlencode($searchValue) ?>&date=<?= urlencode($dateValue) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/footer.php'; ?>