<?php
/**
 * Детальная страница мероприятия
 * Проект: Афиша мероприятий
 * Студент: Барабашов Давид
 */

session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/src/Models/Event.php';

// Получаем ID мероприятия
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id <= 0) {
    die("Ошибка: Неверный ID мероприятия");
}

// Получаем данные мероприятия
$eventModel = new Event($pdo);
$event = $eventModel->getById($event_id);

if (!$event) {
    die("Ошибка: Мероприятие не найдено");
}

$pageTitle = htmlspecialchars($event['title']);
require __DIR__ . '/templates/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <!-- Навигация назад -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($event['title']) ?>
                </li>
            </ol>
        </nav>

        <!-- Карточка мероприятия -->
        <div class="card shadow-sm">
            <?php if ($event['poster_url']): ?>
                <img src="<?= htmlspecialchars($event['poster_url']) ?>" 
                     class="card-img-top" 
                     style="max-height:400px;object-fit:cover;" 
                     alt="<?= htmlspecialchars($event['title']) ?>">
            <?php endif; ?>

            <div class="card-body">
                <h2 class="card-title mb-3"><?= htmlspecialchars($event['title']) ?></h2>

                <div class="mb-3">
                    <h5><i class="bi bi-calendar-event"></i> Дата проведения</h5>
                    <p class="text-muted">
                        <?= htmlspecialchars(date('d.m.Y', strtotime($event['event_date']))) ?>
                    </p>
                </div>

                <?php if (!empty($event['venue'])): ?>
                    <div class="mb-3">
                        <h5><i class="bi bi-geo-alt"></i> Место проведения</h5>
                        <p class="text-muted"><?= htmlspecialchars($event['venue']) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($event['description'])): ?>
                    <div class="mb-3">
                        <h5><i class="bi bi-info-circle"></i> Описание</h5>
                        <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    </div>
                <?php endif; ?>


                <!-- Блок покупки билетов -->
                <div class="mb-4 p-3 border rounded bg-light">
                    <h5 class="mb-3"><i class="bi bi-ticket-perforated"></i> Билеты</h5>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <div class="text-muted">Цена:</div>
                            <div class="fs-5 fw-semibold">
                                <?= htmlspecialchars(number_format((float)($event['price'] ?? 0), 2, '.', ' ')) ?> ₽
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Осталось:</div>
                            <div class="fs-5 fw-semibold">
                                <?= (int)($event['available_tickets'] ?? 0) ?> шт.
                            </div>
                        </div>
                    </div>

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="alert alert-warning mb-0">
                            Чтобы купить билет, нужно <a href="index.php?page=login">войти</a>.
                        </div>
                    <?php else: ?>
                        <?php if ((int)($event['available_tickets'] ?? 0) <= 0): ?>
                            <div class="alert alert-danger mb-0">Билеты закончились.</div>
                        <?php else: ?>
                            <form method="POST" action="index.php?page=buy_tickets" class="row g-2 align-items-end">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">

                                <div class="col-md-4">
                                    <label class="form-label mb-1">Количество</label>
                                    <input type="number" name="quantity" class="form-control" min="1" max="<?= (int)($event['available_tickets'] ?? 0) ?>" value="1" required>
                                </div>

                                <div class="col-md-8">
                                    <button class="btn btn-success w-100">
                                        <i class="bi bi-bag-check"></i> Купить билет
                                    </button>
                                </div>
                            </form>
                            <small class="text-muted d-block mt-2">
                                После покупки билет появится в разделе «Мои билеты».
                            </small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> 
                        Добавлено: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($event['created_at']))) ?>
                    </small>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="card-footer bg-white">
                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Назад к списку
                    </a>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="index.php?page=edit_event&id=<?= (int)$event['id'] ?>" 
                           class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Редактировать
                        </a>

                        <form method="POST" action="index.php?page=delete_event" 
                              onsubmit="return confirm('Удалить мероприятие?');" 
                              class="d-inline">
                            <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
                            <input type="hidden" name="csrf_token" 
                                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <button class="btn btn-danger">
                                <i class="bi bi-trash"></i> Удалить
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
