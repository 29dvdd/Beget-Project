<?php
$pageTitle = 'Мои билеты';
require __DIR__ . '/../templates/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Мои билеты</h1>
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

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            У вас пока нет купленных билетов.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($orders as $order): ?>
                <?php
                $qty = 1;
                if (isset($order['quantity']) && (int)$order['quantity'] > 0) {
                    $qty = (int)$order['quantity'];
                } elseif (isset($order['qty']) && (int)$order['qty'] > 0) {
                    $qty = (int)$order['qty'];
                }

                $sum = 0;
                if (isset($order['total']) && (float)$order['total'] > 0) {
                    $sum = (float)$order['total'];
                } elseif (isset($order['price_at_purchase']) && (float)$order['price_at_purchase'] > 0) {
                    $sum = (float)$order['price_at_purchase'] * $qty;
                } elseif (isset($order['price']) && (float)$order['price'] > 0) {
                    $sum = (float)$order['price'] * $qty;
                }

                $status = isset($order['status']) ? $order['status'] : 'new';
                $badgeClass = 'bg-secondary';
                $statusLabel = $status;

                if ($status === 'new') {
                    $badgeClass = 'bg-warning text-dark';
                    $statusLabel = 'Новый';
                } elseif ($status === 'paid') {
                    $badgeClass = 'bg-success';
                    $statusLabel = 'Оплачен';
                } elseif ($status === 'cancelled' || $status === 'canceled') {
                    $badgeClass = 'bg-danger';
                    $statusLabel = 'Отменён';
                }
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($order['poster_url'])): ?>
                            <img src="<?php echo htmlspecialchars($order['poster_url']); ?>"
                                 class="card-img-top"
                                 style="height: 240px; object-fit: cover;"
                                 alt="<?php echo htmlspecialchars(isset($order['title']) ? $order['title'] : ''); ?>">
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars(isset($order['title']) ? $order['title'] : ''); ?>
                            </h5>

                            <?php if (!empty($order['event_date'])): ?>
                                <p class="mb-1 text-muted">
                                    <strong>Дата:</strong> <?php echo htmlspecialchars($order['event_date']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($order['venue'])): ?>
                                <p class="mb-1 text-muted">
                                    <strong>Место:</strong> <?php echo htmlspecialchars($order['venue']); ?>
                                </p>
                            <?php endif; ?>

                            <p class="mb-1">
                                <strong>Количество:</strong> <?php echo $qty; ?>
                            </p>

                            <p class="mb-1">
                                <strong>Сумма:</strong> <?php echo number_format($sum, 2, '.', ' '); ?> ₽
                            </p>

                            <p class="mb-1 text-muted">
                                <strong>Куплено:</strong> <?php echo htmlspecialchars(isset($order['created_at']) ? $order['created_at'] : ''); ?>
                            </p>

                            <p class="mb-3">
                                <strong>Статус:</strong>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($statusLabel); ?>
                                </span>
                            </p>
                        </div>

                        <div class="card-footer bg-white d-flex flex-wrap gap-2">
                            <?php if (!empty($order['event_id'])): ?>
                                <a href="index.php?page=event_details&id=<?php echo (int)$order['event_id']; ?>"
                                   class="btn btn-primary btn-sm">
                                    Открыть мероприятие
                                </a>
                            <?php endif; ?>

                            <?php if ($status !== 'cancelled' && $status !== 'canceled'): ?>
                                <form method="POST" action="index.php?page=cancel_ticket" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                    <button type="submit"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Отменить этот заказ?');">
                                        Отменить
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>