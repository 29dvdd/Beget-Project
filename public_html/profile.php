<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php?page=login");
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$csrf = $_SESSION['csrf_token'] ?? '';

$sql = "
  SELECT
    o.id AS order_id,
    o.created_at,
    o.status,
    COALESCE(o.qty, o.quantity) AS qty,
    e.id AS event_id,
    e.title,
    e.event_date,
    e.venue,
    e.price,
    e.poster_url
  FROM orders o
  JOIN events e ON e.id = o.event_id
  WHERE o.user_id = ?
  ORDER BY o.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/templates/header.php';
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-ticket-perforated"></i> Мои билеты</h3>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=home">На главную</a>
  </div>

  <?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Операция выполнена.</div>
  <?php endif; ?>

  <?php if (!$orders): ?>
    <div class="alert alert-info">Пока нет покупок.</div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($orders as $o): ?>
        <?php
          $qty = (int)($o['qty'] ?? 1);
          $price = (float)($o['price'] ?? 0);
          $sum = $qty * $price;
          $img = $o['poster_url'] ?: 'https://via.placeholder.com/900x450?text=Афиша';
          $status = $o['status'] ?? 'paid';
          $isCancelled = ($status === 'cancelled');
        ?>

        <div class="col-12 col-md-6 col-lg-5">
          <div class="card shadow-sm h-100">
            <img src="<?= htmlspecialchars($img) ?>"
                 class="card-img-top"
                 style="height:220px;object-fit:cover;"
                 alt="">

            <div class="card-body">
              <h5 class="card-title mb-1"><?= htmlspecialchars($o['title']) ?></h5>
              <div class="text-muted small mb-2">
                Дата: <?= htmlspecialchars(date('d.m.Y', strtotime($o['event_date']))) ?>
              </div>

              <div class="d-flex justify-content-between">
                <div>
                  <div class="text-muted small">Количество</div>
                  <div class="fw-semibold"><?= $qty ?></div>
                </div>
                <div class="text-end">
                  <div class="text-muted small">Сумма</div>
                  <div class="fw-semibold"><?= number_format($sum, 2, '.', ' ') ?> ₽</div>
                </div>
              </div>

              <div class="text-muted small mt-2">
                Куплено: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($o['created_at']))) ?>
                · Статус: <span class="<?= $isCancelled ? 'text-danger' : 'text-success' ?> fw-semibold">
                  <?= htmlspecialchars($status) ?>
                </span>
              </div>
            </div>

            <div class="card-footer bg-white d-flex gap-2">
              <a class="btn btn-primary btn-sm"
                 href="index.php?page=event_details&id=<?= (int)$o['event_id'] ?>">
                Открыть мероприятие
              </a>

              <?php if (!$isCancelled): ?>
                <form method="POST" action="index.php?page=cancel_ticket" class="ms-auto"
                      onsubmit="return confirm('Отменить билет и вернуть количество?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                  <button class="btn btn-danger btn-sm" type="submit">
                    <i class="bi bi-x-circle"></i> Отменить
                  </button>
                </form>
              <?php else: ?>
                <span class="ms-auto badge bg-secondary align-self-center">Отменено</span>
              <?php endif; ?>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>