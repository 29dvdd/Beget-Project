<?php
session_start();
require_once __DIR__ . '/config/db.php';
require __DIR__ . '/check_admin.php';

$csrf = $_SESSION['csrf_token'] ?? '';

$sql = "
  SELECT
    o.id AS order_id,
    o.created_at,
    o.status,
    COALESCE(o.qty, o.quantity) AS qty,
    u.email AS user_email,
    e.title,
    e.event_date,
    e.price,
    e.id AS event_id
  FROM orders o
  JOIN users u ON u.id = o.user_id
  JOIN events e ON e.id = o.event_id
  ORDER BY o.created_at DESC
";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/templates/header.php';
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-list-check"></i> Все заказы</h3>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=home">На главную</a>
  </div>

  <?php if (!$rows): ?>
    <div class="alert alert-info">Заказов пока нет.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Пользователь</th>
            <th>Мероприятие</th>
            <th>Дата</th>
            <th>Кол-во</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th>Куплено</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <?php
              $qty = (int)$r['qty'];
              $sum = $qty * (float)$r['price'];
              $isCancelled = ($r['status'] === 'cancelled');
            ?>
            <tr>
              <td><?= (int)$r['order_id'] ?></td>
              <td><?= htmlspecialchars($r['user_email']) ?></td>
              <td>
                <a href="index.php?page=event_details&id=<?= (int)$r['event_id'] ?>">
                  <?= htmlspecialchars($r['title']) ?>
                </a>
              </td>
              <td><?= htmlspecialchars(date('d.m.Y', strtotime($r['event_date']))) ?></td>
              <td><?= $qty ?></td>
              <td><?= number_format($sum, 2, '.', ' ') ?> ₽</td>
              <td>
                <span class="badge <?= $isCancelled ? 'bg-secondary' : 'bg-success' ?>">
                  <?= htmlspecialchars($r['status']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($r['created_at']))) ?></td>
              <td class="text-end">
                <?php if (!$isCancelled): ?>
                  <form method="POST" action="index.php?page=cancel_ticket"
                        onsubmit="return confirm('Отменить заказ?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="order_id" value="<?= (int)$r['order_id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">
                      <i class="bi bi-x-circle"></i> Отменить
                    </button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>