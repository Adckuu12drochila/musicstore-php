<?php
// src/Views/orders/view.php

use App\Services\Payment\SbpMockGateway;

$title = "Заказ №{$order['id']}";
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/breadcrumbs.php';
require __DIR__ . '/_payment_helpers.php';

// подстрахуемся, если контроллер не передал переменные
$items       = $items       ?? ($order['items'] ?? []);
$sbpPayments = $sbpPayments ?? [];

// Сводные суммы по заказу
$goodsSubtotal = 0.0;
foreach ($items as $it) {
    $goodsSubtotal += $it['unit_price'] * $it['quantity'];
}
$orderDiscount = (float)($order['discount_amount'] ?? 0);
$orderTotal    = max(0, $goodsSubtotal - $orderDiscount);

// --- Логика таймлайна статусов ---
$status        = $order['status'] ?? 'new';
$paymentMethod = $order['payment_method'] ?? 'cod';
$paymentStatus = $order['payment_status'] ?? 'pending';

$paymentComplete =
    ($paymentMethod === 'sbp' && $paymentStatus === 'paid')
    || ($paymentMethod === 'cod' && $status === 'delivered');

// 1 — Создан, 2 — Оплата, 3 — Обработка, 4 — Отправлен, 5 — Доставлен
$progress = 1;

if ($paymentComplete) {
    $progress = max($progress, 2);
}

switch ($status) {
    case 'processing':
        $progress = max($progress, 3);
        break;
    case 'shipped':
        $progress = max($progress, 4);
        break;
    case 'delivered':
        $progress = 5;
        break;
    case 'cancelled':
        // условно считаем, что отменили на этапе обработки
        $progress = max($progress, 3);
        break;
    default:
        // new и прочие — остаётся как есть
        break;
}

$steps = [
    1 => 'Создан',
    2 => 'Оплата',
    3 => 'Обработка',
    4 => 'Отправлен',
    5 => 'Доставлен',
];

// Читаемый статус + класс бейджа
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
<style>
.order-timeline {
  display: flex;
  justify-content: space-between;
  margin: 1rem 0 1.5rem;
}

.order-timeline-step {
  position: relative;
  flex: 1;
  text-align: center;
  font-size: 0.85rem;
}

.order-timeline-step:not(:last-child)::after {
  content: "";
  position: absolute;
  top: 14px;
  right: -50%;
  width: 100%;
  height: 3px;
  background: #dee2e6;
  z-index: 1;
}

.order-timeline-icon {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  margin: 0 auto 0.25rem;
  border: 2px solid #dee2e6;
  background: #fff;
  position: relative;
  z-index: 2;
}

.order-timeline-step.order-step-done .order-timeline-icon {
  background: #198754;
  border-color: #198754;
}

.order-timeline-step.order-step-current .order-timeline-icon {
  background: #0d6efd;
  border-color: #0d6efd;
}

.order-timeline-step.order-step-todo .order-timeline-icon {
  background: #fff;
  border-color: #dee2e6;
}

.order-timeline-step.order-step-done::after {
  background: #198754;
}

.order-timeline-step.order-step-current::after {
  background: #0d6efd;
}

.order-timeline-label {
  margin-top: 0.1rem;
}
</style>

<h2 class="mb-4">Заказ №<?= (int)$order['id'] ?></h2>

<!-- Карточка с таймлайном и основными данными -->
<div class="profile-card p-4 mb-4">
  <div class="order-timeline">
    <?php foreach ($steps as $i => $label): ?>
      <?php
        $stepClass = 'order-timeline-step';
        if ($i < $progress) {
            $stepClass .= ' order-step-done';
        } elseif ($i === $progress) {
            $stepClass .= ' order-step-current';
        } else {
            $stepClass .= ' order-step-todo';
        }
      ?>
      <div class="<?= $stepClass ?>">
        <div class="order-timeline-icon"></div>
        <div class="order-timeline-label">
          <?= htmlspecialchars($label, ENT_QUOTES) ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (($order['status'] ?? '') === 'cancelled'): ?>
    <p class="text-danger fw-semibold mb-3">Заказ отменён.</p>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-6">
      <p><strong>Дата:</strong>
        <?= htmlspecialchars(date('d.m.Y H:i', strtotime($order['created_at']))) ?></p>

      <p><strong>Статус заказа:</strong>
        <span class="<?= $statusClass ?>">
          <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
        </span>
      </p>

      <p><strong>Способ оплаты:</strong>
        <?= paymentMethodLabel($order['payment_method'] ?? 'cod') ?></p>

      <p><strong>Статус оплаты:</strong>
        <?= paymentStatusBadgeHtml(
              $order['payment_method'] ?? 'cod',
              $order['payment_status'] ?? 'pending'
        ) ?>
      </p>

      <?php if (!empty($order['paid_at'])): ?>
        <p><strong>Оплачен:</strong>
          <?= htmlspecialchars(date('d.m.Y H:i', strtotime($order['paid_at']))) ?>
        </p>
      <?php endif; ?>
    </div>

    <div class="col-md-6">
      <?php if (!empty($order['coupon_code'])): ?>
        <p><strong>Промокод:</strong>
          <?= htmlspecialchars($order['coupon_code'], ENT_QUOTES) ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($order['discount_amount']) && (float)$order['discount_amount'] > 0): ?>
        <p><strong>Скидка по промокоду:</strong>
          <?= number_format((float)$order['discount_amount'], 2, ',', ' ') ?> ₽
        </p>
      <?php endif; ?>

      <p><strong>Сумма по товарам:</strong>
        <?= number_format($goodsSubtotal, 2, ',', ' ') ?> ₽</p>

      <?php if ($orderDiscount > 0): ?>
        <p class="mb-0"><strong>Итого к оплате:</strong>
          <?= number_format($orderTotal, 2, ',', ' ') ?> ₽</p>
      <?php else: ?>
        <p class="mb-0"><strong>Итого:</strong>
          <?= number_format($goodsSubtotal, 2, ',', ' ') ?> ₽</p>
      <?php endif; ?>
    </div>
  </div>

  <hr>

  <p class="mb-1"><strong>Получатель:</strong>
    <?= htmlspecialchars($order['client_name']) ?></p>
  <p class="mb-1"><strong>Телефон:</strong>
    <?= htmlspecialchars($order['phone']) ?></p>
  <p class="mb-0"><strong>Адрес доставки:</strong><br>
    <?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
</div>

<!-- СБП-платежи (если применимо) -->
<?php if (($order['payment_method'] ?? 'cod') === 'sbp'): ?>
  <div class="profile-card p-4 mb-4">
    <h4 class="mb-3">Оплата по СБП</h4>

    <?php if (empty($sbpPayments)): ?>
      <p class="text-muted mb-0">
        Для этого заказа ещё не создавались платежи по СБП.
      </p>
    <?php else: ?>
      <div class="table-responsive mb-2">
        <table class="table table-sm align-middle mb-0">
          <thead>
            <tr>
              <th>Дата создания</th>
              <th>Внешний ID</th>
              <th>Сумма платежа</th>
              <th>Статус платежа</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sbpPayments as $p): ?>
              <?php
                $pStatus   = $p['status'] ?? 'pending';
                $createdAt = $p['created_at'] ?? null;

                // Для истории: если платеж всё ещё pending, но TTL истёк, показываем его как "expired"
                if ($pStatus === 'pending' && $createdAt) {
                    $createdTs = strtotime($createdAt);
                    $expiresTs = $createdTs + SbpMockGateway::TTL_SECONDS;
                    if (time() >= $expiresTs) {
                        $pStatus = 'expired';
                    }
                }
              ?>
              <tr>
                <td>
                  <?= $createdAt
                        ? htmlspecialchars(date('d.m.Y H:i', strtotime($createdAt)))
                        : '' ?>
                </td>
                <td>
                  <code><?= htmlspecialchars($p['external_id'] ?? '') ?></code>
                </td>
                <td>
                  <?= number_format((float)($p['amount'] ?? 0), 2, ',', ' ') ?> ₽
                </td>
                <td>
                  <?= paymentStatusBadgeHtml('sbp', $pStatus) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($orderDiscount > 0): ?>
        <p class="text-muted small mb-0">
          Сумма платежа указана с учётом скидки по промокоду:
          <?= number_format($goodsSubtotal, 2, ',', ' ') ?> ₽
          − <?= number_format($orderDiscount, 2, ',', ' ') ?> ₽
          = <?= number_format($orderTotal, 2, ',', ' ') ?> ₽.
        </p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- Состав заказа -->
<div class="profile-card p-4 mb-4">
  <h4 class="mb-3">Состав заказа</h4>

  <?php if (empty($items)): ?>
    <p class="mb-0">Нет позиций в заказе.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th>Товар</th>
            <th>Цена</th>
            <th>Кол-во</th>
            <th>Сумма</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <?php $subtotal = $it['unit_price'] * $it['quantity']; ?>
            <tr>
              <td><?= htmlspecialchars($it['name']) ?></td>
              <td><?= number_format($it['unit_price'], 2, ',', ' ') ?> ₽</td>
              <td><?= (int)$it['quantity'] ?></td>
              <td><?= number_format($subtotal, 2, ',', ' ') ?> ₽</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<p>
  <a href="/orders" class="btn btn-secondary">← Назад к истории заказов</a>
</p>

<?php require __DIR__ . '/../layout/footer.php'; ?>
