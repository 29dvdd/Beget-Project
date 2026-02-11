<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить мероприятие</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4" style="max-width: 720px;">

    <div class="d-flex justify-content-between mb-3">
        <h3>Добавить мероприятие</h3>
        <a href="?page=home" class="btn btn-outline-secondary btn-sm">Назад</a>
    </div>

    <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success">
            Мероприятие добавлено!
        </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" novalidate>

                <div class="mb-3">
                    <label class="form-label">Название *</label>
                    <input class="form-control" name="title" required
                           value="<?= htmlspecialchars($old['title'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Цена (₽) *</label>
                    <input class="form-control" type="number" step="0.01" name="price" required
                           value="<?= htmlspecialchars($old['price'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Количество билетов *</label>
                    <input class="form-control" type="number" name="available_tickets" min="0" required
                           value="<?= htmlspecialchars($old['available_tickets'] ?? '0') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Дата мероприятия *</label>
                    <input class="form-control" type="date" name="event_date" required
                           value="<?= htmlspecialchars($old['event_date'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Адрес проведения</label>
                    <input class="form-control" type="text" name="address"
                           value="<?= htmlspecialchars($old['address'] ?? '') ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Широта (latitude)</label>
                        <input class="form-control" type="number" step="0.00000001" name="latitude"
                               value="<?= htmlspecialchars($old['latitude'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Долгота (longitude)</label>
                        <input class="form-control" type="number" step="0.00000001" name="longitude"
                               value="<?= htmlspecialchars($old['longitude'] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ссылка на изображение</label>
                    <input class="form-control" name="image_url"
                           value="<?= htmlspecialchars($old['image_url'] ?? '') ?>">
                </div>

                <button class="btn btn-success w-100" type="submit">
                    Добавить мероприятие
                </button>

            </form>
        </div>
    </div>

</div>
</body>
</html>
