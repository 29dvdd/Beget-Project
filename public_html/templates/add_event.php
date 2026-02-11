<?php
session_start();
require_once __DIR__ . '/../config/Database.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("ДОСТУП ЗАПРЕЩЕН. У вас нет прав администратора. <a href='?page=login'>Войти</a>");
}

// Функция для безопасного вывода
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$error = $error ?? null;
$success = $success ?? null;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить мероприятие</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="?page=home">🎭 Афиша</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Вы вошли как: <b><?= h($_SESSION['role']) ?></b>
                </span>
                <a href="?page=events" class="btn btn-outline-light btn-sm me-2">К афише</a>
                <a href="?page=logout" class="btn btn-outline-light btn-sm">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">➕ Добавить новое мероприятие</h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Сообщения -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= h($error) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= h($success) ?></div>
                        <?php endif; ?>

                        <!-- Форма добавления -->
                        <form method="POST" action="?page=add_event">
                            <div class="mb-3">
                                <label class="form-label">Название мероприятия *</label>
                                <input type="text" name="title" class="form-control" required 
                                       value="<?= h($_POST['title'] ?? '') ?>"
                                       placeholder="Например: Концерт классической музыки">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Дата проведения *</label>
                                <input type="date" name="event_date" class="form-control" required 
                                       value="<?= h($_POST['event_date'] ?? '') ?>"
                                       min="<?= date('Y-m-d') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Место проведения</label>
                                <input type="text" name="venue" class="form-control" 
                                       value="<?= h($_POST['venue'] ?? '') ?>"
                                       placeholder="Например: Большой театр, г. Москва">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Описание</label>
                                <textarea name="description" class="form-control" rows="4" 
                                          placeholder="Подробное описание мероприятия..."><?= h($_POST['description'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">💾 Сохранить мероприятие</button>
                                <a href="?page=events" class="btn btn-secondary">Отмена</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Подсказка -->
                <div class="alert alert-info mt-3">
                    <small>
                        💡 <strong>Подсказка:</strong> После создания мероприятия вы сможете загрузить постер на странице редактирования.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
