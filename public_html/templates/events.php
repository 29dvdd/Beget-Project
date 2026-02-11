<?php
require_once __DIR__ . '/../config/Database.php';

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Функция для безопасного вывода
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$success = $_GET['success'] ?? null;
$date_filter = $_GET['event_date'] ?? '';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Афиша мероприятий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="?page=home">🎭 Афиша</a>
            <div class="d-flex">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="navbar-text text-white me-3">
                        Вы вошли как: <b><?= h($_SESSION['role'] ?? 'User') ?></b>
                    </span>
                    <a href="?page=profile" class="btn btn-outline-light btn-sm me-2">Профиль</a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="?page=add_event" class="btn btn-success btn-sm me-2">+ Добавить</a>
                    <?php endif; ?>
                    <a href="?page=logout" class="btn btn-outline-light btn-sm">Выйти</a>
                <?php else: ?>
                    <a href="?page=login" class="btn btn-primary btn-sm me-2">Войти</a>
                    <a href="?page=register" class="btn btn-outline-primary btn-sm">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Сообщение об успехе -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Операция выполнена успешно!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Форма фильтрации -->
        <div class="card mb-4 p-3 bg-light">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="events">
                <div class="col-md-6">
                    <label class="form-label">Фильтр по дате:</label>
                    <input type="date" name="event_date" class="form-control" value="<?= h($date_filter) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">🔍 Найти</button>
                        <a href="?page=events" class="btn btn-secondary">Сбросить</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Список мероприятий -->
        <h2 class="mb-4">📅 Мероприятия</h2>
        
        <?php if (count($events) > 0): ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <!-- Постер мероприятия -->
                            <?php if ($event['poster_url']): ?>
                                <img src="<?= h($event['poster_url']) ?>" class="card-img-top" alt="Постер" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x200/6c757d/ffffff?text=Нет+постера" class="card-img-top" alt="Заглушка" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= h($event['title']) ?></h5>
                                <p class="card-text text-muted small">
                                    📅 <?= date('d.m.Y', strtotime($event['event_date'])) ?>
                                </p>
                                <?php if ($event['venue']): ?>
                                    <p class="card-text text-muted small">
                                        📍 <?= h($event['venue']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="card-text"><?= h(substr($event['description'], 0, 100)) ?>...</p>
                                
                                <div class="mt-auto">
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                                        <div class="btn-group w-100" role="group">
                                            <a href="?page=edit_event&id=<?= $event['id'] ?>" class="btn btn-outline-primary btn-sm">✏️</a>
                                            <form method="POST" action="?page=delete_event" class="d-inline" onsubmit="return confirm('Удалить мероприятие?')">
                                                <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">🗑️</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-primary w-100" disabled>🎫 Купить билет</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="text-muted">
                    <h4>📭 Мероприятий не найдено</h4>
                    <p>Попробуйте изменить фильтр или добавьте новое мероприятие.</p>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="?page=add_event" class="btn btn-success mt-3">+ Добавить мероприятие</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
