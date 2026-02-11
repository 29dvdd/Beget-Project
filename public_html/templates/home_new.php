<?php
require_once __DIR__ . '/../config/Database.php';

// Временное решение: прямое подключение к БД
$host = 'localhost';
$db   = 'b9628214_test';
$user = 'b9628214_test';
$pass = 'Parol123';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Временная модель Event
class TempEvent {
    private PDO $db;
    
    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }
    
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM events ORDER BY event_date ASC");
        return $stmt->fetchAll();
    }
}

// Функция для безопасного вывода
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Получаем ближайшие мероприятия
$events = (new TempEvent())->getAll();
$upcoming_events = array_filter($events, function($event) {
    return $event['event_date'] >= date('Y-m-d');
});

// Берем только 3 ближайших мероприятия
$upcoming_events = array_slice($upcoming_events, 0, 3);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Афиша мероприятий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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

    <!-- Hero секция -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">🎭 Добро пожаловать в Афишу</h1>
            <p class="lead mb-4">Откройте для себя лучшие мероприятия вашего города</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="?page=events" class="btn btn-light btn-lg">📅 Смотреть афишу</a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="?page=register" class="btn btn-outline-light btn-lg">🎟️ Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Ближайшие мероприятия -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">🔥 Ближайшие мероприятия</h2>
                <p class="text-muted">Не пропустите самые интересные события</p>
            </div>
            
            <?php if (count($upcoming_events) > 0): ?>
                <div class="row g-4">
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm feature-card">
                                <?php if ($event['poster_url']): ?>
                                    <img src="<?= h($event['poster_url']) ?>" class="card-img-top" alt="Постер" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/400x200/6c757d/ffffff?text=Нет+постера" class="card-img-top" alt="Заглушка" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?= h($event['title']) ?></h5>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            📅 <?= date('d.m.Y', strtotime($event['event_date'])) ?>
                                            <?php if ($event['venue']): ?>
                                                📍 <?= h($event['venue']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </p>
                                    <p class="card-text"><?= h(substr($event['description'], 0, 100)) ?>...</p>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <a href="?page=events" class="btn btn-primary w-100">Подробнее</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="text-muted">
                        <h4>📭 Пока нет мероприятий</h4>
                        <p>Следите за обновлениями!</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="?page=events" class="btn btn-outline-primary btn-lg">📋 Посмотреть все мероприятия</a>
            </div>
        </div>
    </section>

    <!-- Возможности -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">✨ Возможности платформы</h2>
                <p class="text-muted">Что вы можете делать на нашей афише</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="display-4 text-primary mb-3">🔍</div>
                        <h5 class="card-title">Поиск по дате</h5>
                        <p class="card-text">Удобная фильтрация мероприятий по дате проведения</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="display-4 text-success mb-3">🖼️</div>
                        <h5 class="card-title">Постеры мероприятий</h5>
                        <p class="card-text">Красочные постеры для каждого мероприятия</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="display-4 text-warning mb-3">⚙️</div>
                        <h5 class="card-title">Управление</h5>
                        <p class="card-text">Полный контроль над мероприятиями для администраторов</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">© 2026 🎭 Афиша мероприятий. Все права защищены.</p>
            <p class="mb-0 small text-muted">Курсовой проект Барабашова Давида</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
