<?php
// src/Views/orders/index.php
$title = 'История заказов';
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/breadcrumbs.php';
require __DIR__ . '/_payment_helpers.php';

// Подстрахуемся
$orders = $orders ?? [];
?>

<h2 class="mb-4">История заказов</h2>

<div class="profile-card p-4">
  <?php if (empty($orders)): ?>
    <p class="mb-0">
      У вас ещё нет заказов.
      <a href="/products">Перейти в каталог</a>.
    </p>
  <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="text-muted small">
        Найдено заказов: <strong><?= (int)count($orders) ?></strong>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>№ заказа</th>
            <th>Дата</th>
            <th class="text-end">Сумма</th>
            <th>Статус</th>
            <th>Оплата</th>
            <th>Способ оплаты</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <?php
              // Сумма с учётом скидки / total_amount
              $discount = (float)($order['discount_amount'] ?? 0);

              if (isset($order['total_amount']) && $order['total_amount'] !== null && (float)$order['total_amount'] > 0) {
                  $total = (float)$order['total_amount'];
                  $baseTotal = isset($order['total']) ? (float)$order['total'] : $total;
              } else {
                  $baseTotal = isset($order['total']) ? (float)$order['total'] : 0.0;
                  $total     = max(0, $baseTotal - $discount);
              }

              $status        = $order['status'] ?? 'new';
              $paymentMethod = $order['payment_method'] ?? 'cod';
              $paymentStatus = $order['payment_status'] ?? 'pending';

              // Читаемое название статуса + класс бейджа
              $statusLabel = match ($status) {
                  'new'        => 'Новый',
                  'processing' => 'В обработке',
                  'shipped'    => 'Отправлен',
                  'delivered'  => 'Доставлен',
                  'cancelled'  => 'Отменён',
                  default      => ucfirst($status),
              };

              $statusClass = match ($status) {
                  'new'        => 'badge bg-warning text-dark',
                  'processing' => 'badge bg-info text-dark',
                  'shipped'    => 'badge bg-primary',
                  'delivered'  => 'badge bg-success',
                  'cancelled'  => 'badge bg-secondary',
                  default      => 'badge bg-light text-dark',
              };
            ?>
            <tr>
              <td>#<?= htmlspecialchars((string)$order['id'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>

              <td class="text-end">
                <?= number_format($total, 2, ',', ' ') ?> ₽
                <?php if ($discount > 0 && $baseTotal > $total): ?>
                  <div class="text-muted small">
                    (было <?= number_format($baseTotal, 2, ',', ' ') ?> ₽,
                    скидка −<?= number_format($discount, 2, ',', ' ') ?> ₽)
                  </div>
                <?php endif; ?>
              </td>

              <td>
                <span class="<?= $statusClass ?>">
                  <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                </span>
              </td>

              <td>
                <?= paymentStatusBadgeHtml($paymentMethod, $paymentStatus) ?>
              </td>

              <td>
                <?= htmlspecialchars(
                      paymentMethodLabel($paymentMethod),
                      ENT_QUOTES,
                      'UTF-8'
                    ) ?>
              </td>

              <td class="text-end">
                <a href="/orders/<?= (int)$order['id'] ?>"
                   class="btn btn-sm btn-outline-primary">
                  Просмотр
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
