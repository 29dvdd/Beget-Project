<?php
$pageTitle = !empty($isCreate) ? 'Добавить мероприятие' : 'Редактировать мероприятие';
require __DIR__ . '/../templates/header.php';

$eventId = isset($event['id']) ? (int)$event['id'] : 0;
$title = isset($event['title']) ? $event['title'] : '';
$description = isset($event['description']) ? $event['description'] : '';
$eventDate = isset($event['event_date']) ? substr($event['event_date'], 0, 10) : '';
$venue = isset($event['venue']) ? $event['venue'] : '';
$price = isset($event['price']) ? $event['price'] : '';
$availableTickets = isset($event['available_tickets']) ? $event['available_tickets'] : '';
$posterUrl = isset($event['poster_url']) ? $event['poster_url'] : '';

$formAction = !empty($isCreate)
    ? 'index.php?page=add_event'
    : 'index.php?page=update_event';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">
            <?php echo !empty($isCreate) ? 'Добавить мероприятие' : 'Редактировать мероприятие'; ?>
        </h1>
        <a href="index.php?page=home" class="btn btn-outline-secondary">На главную</a>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($flashSuccess); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($flashError); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($formAction); ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <?php if (empty($isCreate)): ?>
                    <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Название мероприятия *</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           value="<?php echo htmlspecialchars($title); ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea name="description"
                              class="form-control"
                              rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Дата проведения *</label>
                        <input type="date"
                               name="event_date"
                               class="form-control"
                               value="<?php echo htmlspecialchars($eventDate); ?>"
                               required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Место проведения</label>
                        <input type="text"
                               name="venue"
                               class="form-control"
                               value="<?php echo htmlspecialchars($venue); ?>">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Цена</label>
                        <input type="number"
                               name="price"
                               class="form-control"
                               step="0.01"
                               min="0"
                               value="<?php echo htmlspecialchars($price); ?>">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Билеты</label>
                        <input type="number"
                               name="available_tickets"
                               class="form-control"
                               min="0"
                               value="<?php echo htmlspecialchars($availableTickets); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Постер</label>
                    <input type="file"
                           name="poster"
                           class="form-control"
                           accept=".jpg,.jpeg,.png,.gif,.webp">
                </div>

                <?php if (!empty($posterUrl)): ?>
                    <div class="mb-3">
                        <div class="small text-muted mb-2">Текущий постер:</div>
                        <img src="<?php echo htmlspecialchars($posterUrl); ?>"
                             alt="Постер"
                             class="img-fluid rounded border"
                             style="max-width: 320px; max-height: 320px; object-fit: cover;">
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">
                        <?php echo !empty($isCreate) ? 'Добавить мероприятие' : 'Сохранить изменения'; ?>
                    </button>

                    <a href="index.php?page=home" class="btn btn-secondary">Отмена</a>
                </div>
            </form>

            <?php if (empty($isCreate) && $eventId > 0): ?>
                <hr>
                <form method="POST"
                      action="index.php?page=delete_event"
                      onsubmit="return confirm('Удалить это мероприятие?');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                    <button type="submit" class="btn btn-danger">
                        Удалить мероприятие
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>