<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/src/Models/Event.php';
require __DIR__ . '/templates/header.php';

$eventModel = new Event($pdo);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  echo "<div class='container'><div class='alert alert-danger'>Неверный ID</div></div>";
  require __DIR__ . '/templates/footer.php';
  exit;
}

/**
 * ВНИМАНИЕ:
 * В твоей модели метод может называться иначе.
 * Если getById() нет — замени на реальное имя.
 */
if (method_exists($eventModel, 'getById')) {
  $event = $eventModel->getById($id);
} elseif (method_exists($eventModel, 'getOne')) {
  $event = $eventModel->getOne($id);
} else {
  $event = null;
}

if (!$event) {
  echo "<div class='container'><div class='alert alert-danger'>Мероприятие не найдено</div></div>";
  require __DIR__ . '/templates/footer.php';
  exit;
}

$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <h3 class="text-center mb-4">Редактировать мероприятие</h3>

      <form class="card shadow-sm p-4"
            method="POST"
            action="index.php?page=update_event"
            enctype="multipart/form-data">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int)$event['id'] ?>">

        <div class="mb-3">
          <label class="form-label">Название *</label>
          <input class="form-control" name="title" required
                 value="<?= htmlspecialchars($event['title'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Описание</label>
          <textarea class="form-control" name="description" rows="5"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Дата мероприятия *</label>
          <!-- type=date ДОЛЖЕН получать YYYY-MM-DD -->
          <input type="date" class="form-control" name="event_date" required
                 value="<?= htmlspecialchars($event['event_date'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Место проведения</label>
          <input class="form-control" name="venue"
                 value="<?= htmlspecialchars($event['venue'] ?? '') ?>">
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Цена билета (₽)</label>
            <input type="number" step="0.01" min="0" class="form-control" name="price"
                   value="<?= htmlspecialchars($event['price'] ?? 0) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Доступно билетов</label>
            <input type="number" min="0" class="form-control" name="available_tickets"
                   value="<?= htmlspecialchars($event['available_tickets'] ?? 0) ?>">
          </div>
        </div>

        <hr class="my-4">

        <div class="row g-3 align-items-start">
          <div class="col-md-5">
            <div class="fw-semibold mb-2">Текущая афиша</div>
            <div class="border rounded bg-light p-2 text-center">
              <?php if (!empty($event['poster_url'])): ?>
                <img src="<?= htmlspecialchars($event['poster_url']) ?>"
                     class="rounded"
                     style="max-width:100%;height:180px;object-fit:cover;"
                     alt="">
              <?php else: ?>
                <div class="text-muted">Нет постера</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="col-md-7">
            <label class="form-label">Загрузить новую афишу (JPG/PNG/GIF, макс. 5MB)</label>
            <input type="file" class="form-control" name="poster" accept=".jpg,.jpeg,.png,.gif">
            <div class="form-text">Если файл не выбирать — останется текущий постер.</div>
          </div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <a class="btn btn-outline-secondary" href="index.php?page=home">
            <i class="bi bi-arrow-left"></i> Назад
          </a>
          <button class="btn btn-success w-100" type="submit">
            <i class="bi bi-check2-circle"></i> Сохранить изменения
          </button>
        </div>

      </form>

    </div>
  </div>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>