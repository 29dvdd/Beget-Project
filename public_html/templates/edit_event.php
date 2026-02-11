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
    <title>Редактировать мероприятие</title>
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
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">✏️ Редактировать мероприятие</h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Сообщения -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= h($error) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= h($success) ?></div>
                        <?php endif; ?>

                        <!-- Текущий постер -->
                        <?php if ($event['poster_url']): ?>
                            <div class="mb-3">
                                <label class="form-label">Текущий постер:</label>
                                <div>
                                    <img src="<?= h($event['poster_url']) ?>" alt="Постер" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Форма редактирования -->
                        <form method="POST" action="?page=edit_event&id=<?= $event['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Название мероприятия *</label>
                                <input type="text" name="title" class="form-control" required 
                                       value="<?= h($event['title']) ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Дата проведения *</label>
                                <input type="date" name="event_date" class="form-control" required 
                                       value="<?= h($event['event_date']) ?>"
                                       min="<?= date('Y-m-d') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Место проведения</label>
                                <input type="text" name="venue" class="form-control" 
                                       value="<?= h($event['venue']) ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Описание</label>
                                <textarea name="description" class="form-control" rows="4"><?= h($event['description']) ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">💾 Сохранить изменения</button>
                                <a href="?page=events" class="btn btn-secondary">Отмена</a>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <!-- Форма загрузки постера -->
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">🖼️ Загрузить новый постер</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="upload_poster.php" enctype="multipart/form-data">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Выберите изображение:</label>
                                        <input type="file" name="poster" class="form-control" accept="image/*" required>
                                        <small class="text-muted">Допустимые форматы: JPG, PNG, GIF. Максимальный размер: 5MB</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-info">📤 Загрузить постер</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
