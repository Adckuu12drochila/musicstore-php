<?php
// src/Views/profile/index.php

use App\Services\Payment\SbpMockGateway;

// хелперы оплаты для бейджей
require __DIR__ . '/../orders/_payment_helpers.php';

$title = 'Мой профиль';
require __DIR__ . '/../layout/header.php';

// Подстрахуемся
$user   = $user   ?? [];
$orders = $orders ?? [];

// Имя / email / инициалы
$displayName = trim($user['name'] ?? '');
$email       = trim($user['email'] ?? '');
$initials    = '';

if ($displayName !== '') {
    $parts = preg_split('/\s+/u', $displayName);
    foreach ($parts as $p) {
        if ($p !== '') {
            $initials .= mb_substr($p, 0, 1);
        }
        if (mb_strlen($initials) >= 2) {
            break;
        }
    }
} elseif ($email !== '') {
    $initials = mb_strtoupper(mb_substr($email, 0, 1));
}

if ($initials === '') {
    $initials = 'U';
}

// Простая статистика по заказам (по всем)
$ordersCount     = count($orders);
$ordersPaidCount = 0;
$ordersTotalSum  = 0.0;

foreach ($orders as $o) {
    $items = $o['items'] ?? [];
    $subtotal = 0.0;
    foreach ($items as $it) {
        $subtotal += $it['unit_price'] * $it['quantity'];
    }

    $discount = (float)($o['discount_amount'] ?? 0);
    if (isset($o['total_amount']) && $o['total_amount'] !== null && (float)$o['total_amount'] > 0) {
        $total = (float)$o['total_amount'];
    } else {
        $total = max(0, $subtotal - $discount);
    }

    $ordersTotalSum += $total;

    $status        = $o['status'] ?? 'new';
    $paymentMethod = $o['payment_method'] ?? 'cod';
    $paymentStatus = $o['payment_status'] ?? 'pending';

    $paymentComplete =
        ($paymentMethod === 'sbp' && $paymentStatus === 'paid') ||
        ($paymentMethod === 'cod' && $status === 'delivered');

    if ($paymentComplete) {
        $ordersPaidCount++;
    }
}

// Разделяем заказы на активные и историю
$activeOrders  = [];
$historyOrders = [];

foreach ($orders as $o) {
    $status = $o['status'] ?? 'new';
    if (in_array($status, ['delivered', 'cancelled'], true)) {
        $historyOrders[] = $o;
    } else {
        $activeOrders[] = $o;
    }
}
?>

<h2 class="mb-4"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>

<div class="row g-4 mb-4 align-items-stretch">
  <div class="col-md-6">
    <div class="profile-card p-3 d-flex align-items-center">
      <div class="profile-avatar me-3">
        <span><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <div>
        <div class="fw-semibold mb-1">
          <?= $displayName !== ''
                ? htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8')
                : 'Безымянный пользователь' ?>
        </div>
        <?php if ($email !== ''): ?>
          <div class="text-muted small">
            <i class="bi bi-envelope me-1"></i>
            <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="profile-card p-3">
      <div class="row text-center">
        <div class="col-4">
          <div class="text-muted small mb-1">Всего заказов</div>
          <div class="fs-5 fw-semibold"><?= (int)$ordersCount ?></div>
        </div>
        <div class="col-4">
          <div class="text-muted small mb-1">Оплачено</div>
          <div class="fs-5 fw-semibold"><?= (int)$ordersPaidCount ?></div>
        </div>
        <div class="col-4">
          <div class="text-muted small mb-1">Сумма покупок</div>
          <div class="fs-6 fw-semibold">
            <?= $ordersTotalSum > 0
                  ? number_format($ordersTotalSum, 2, ',', ' ') . ' ₽'
                  : '—' ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Личные данные и безопасность -->
  <div class="col-lg-4 col-md-5 d-flex flex-column gap-4">

    <!-- Личные данные (карточка по содержимому, без растягивания вниз) -->
    <div class="profile-card p-4">
      <h4 class="mb-3">Личные данные</h4>
      <form action="/profile" method="post">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input
            type="email"
            class="form-control"
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
            readonly
          >
        </div>
        <div class="mb-3">
          <label class="form-label">Имя</label>
          <input
            type="text"
            name="name"
            class="form-control"
            value="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>"
            required
          >
        </div>
        <button type="submit" class="btn btn-primary">
          Сохранить изменения
        </button>
      </form>
    </div>

    <!-- Смена пароля (тоже компактная карточка) -->
    <div class="profile-card p-4">
      <h4 class="mb-3">Сменить пароль</h4>
      <form action="/profile/password" method="post">
        <div class="mb-3">
          <label class="form-label">Старый пароль</label>
          <input type="password" name="old_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Новый пароль</label>
          <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Повтор нового пароля</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-warning">
          Изменить пароль
        </button>
      </form>
    </div>

  </div>

  <!-- Заказы: шире, две карточки -->
  <div class="col-lg-8 col-md-7 d-flex flex-column gap-4">

    <!-- Активные заказы -->
    <div class="profile-card p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Активные заказы</h4>
        <?php if (!empty($activeOrders)): ?>
          <span class="badge bg-secondary">
            <?= (int)count($activeOrders) ?> шт.
          </span>
        <?php endif; ?>
      </div>

      <?php if (empty($activeOrders)): ?>
        <p class="text-muted mb-0">
          Активных заказов нет.
        </p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>№</th>
                <th>Дата</th>
                <th>Статус</th>
                <th>Оплата</th>
                <th class="text-end">Сумма</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($activeOrders as $o): ?>
              <?php
                $items    = $o['items'] ?? [];
                $subtotal = 0.0;
                foreach ($items as $it) {
                    $subtotal += $it['unit_price'] * $it['quantity'];
                }
                $discount = (float)($o['discount_amount'] ?? 0);
                if (isset($o['total_amount']) && $o['total_amount'] !== null && (float)$o['total_amount'] > 0) {
                    $total = (float)$o['total_amount'];
                } else {
                    $total = max(0, $subtotal - $discount);
                }

                $status        = $o['status'] ?? 'new';
                $paymentMethod = $o['payment_method'] ?? 'cod';
                $paymentStatus = $o['payment_status'] ?? 'pending';

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
                <td>#<?= (int)$o['id'] ?></td>
                <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($o['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <span class="<?= $statusClass ?>">
                    <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                  </span>
                </td>
                <td>
                  <div class="small">
                    <?= paymentMethodLabel($paymentMethod) ?><br>
                    <?= paymentStatusBadgeHtml($paymentMethod, $paymentStatus) ?>
                  </div>
                </td>
                <td class="text-end">
                  <?= number_format($total, 2, ',', ' ') ?> ₽
                  <?php if ($discount > 0): ?>
                    <div class="text-muted small">
                      (−<?= number_format($discount, 2, ',', ' ') ?> ₽ по промокоду)
                    </div>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <a href="/orders/<?= (int)$o['id'] ?>"
                     class="btn btn-sm btn-outline-secondary">
                    Подробнее
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- История заказов (с прокруткой) -->
    <div class="profile-card p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">История заказов</h4>
        <?php if (!empty($historyOrders)): ?>
          <span class="badge bg-secondary">
            <?= (int)count($historyOrders) ?> шт.
          </span>
        <?php endif; ?>
      </div>

      <?php if (empty($historyOrders)): ?>
        <p class="text-muted mb-0">
          Исторических заказов пока нет.
        </p>
      <?php else: ?>
        <div class="profile-orders-history table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>№</th>
                <th>Дата</th>
                <th>Статус</th>
                <th>Оплата</th>
                <th class="text-end">Сумма</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($historyOrders as $o): ?>
              <?php
                $items    = $o['items'] ?? [];
                $subtotal = 0.0;
                foreach ($items as $it) {
                    $subtotal += $it['unit_price'] * $it['quantity'];
                }
                $discount = (float)($o['discount_amount'] ?? 0);
                if (isset($o['total_amount']) && $o['total_amount'] !== null && (float)$o['total_amount'] > 0) {
                    $total = (float)$o['total_amount'];
                } else {
                    $total = max(0, $subtotal - $discount);
                }

                $status        = $o['status'] ?? 'new';
                $paymentMethod = $o['payment_method'] ?? 'cod';
                $paymentStatus = $o['payment_status'] ?? 'pending';

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
                <td>#<?= (int)$o['id'] ?></td>
                <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($o['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <span class="<?= $statusClass ?>">
                    <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                  </span>
                </td>
                <td>
                  <div class="small">
                    <?= paymentMethodLabel($paymentMethod) ?><br>
                    <?= paymentStatusBadgeHtml($paymentMethod, $paymentStatus) ?>
                  </div>
                </td>
                <td class="text-end">
                  <?= number_format($total, 2, ',', ' ') ?> ₽
                  <?php if ($discount > 0): ?>
                    <div class="text-muted small">
                      (−<?= number_format($discount, 2, ',', ' ') ?> ₽ по промокоду)
                    </div>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <a href="/orders/<?= (int)$o['id'] ?>"
                     class="btn btn-sm btn-outline-secondary">
                    Подробнее
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
