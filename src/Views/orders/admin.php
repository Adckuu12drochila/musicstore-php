<?php
// src/Views/orders/admin.php

$title = 'Управление заказами';
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/_payment_helpers.php';

// подстрахуемся на случай, если что-то не передали
$filters       = $filters       ?? [
    'date_from'      => '',
    'date_to'        => '',
    'payment_method' => '',
    'payment_status' => '',
    'order_status'   => '',
];
$stats         = $stats         ?? [
    'total_count'   => 0,
    'total_sum'     => 0.0,
    'cod_count'     => 0,
    'sbp_count'     => 0,
    'paid_count'    => 0,
    'pending_count' => 0,
];
$activeOrders  = $activeOrders  ?? [];
$historyOrders = $historyOrders ?? [];
?>

<h2 class="mb-4">Админ: заказы</h2>

<!-- Мини-дашборд -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="small text-muted">Заказов (по фильтру)</div>
        <div class="h4 mb-0"><?= (int)$stats['total_count'] ?></div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="small text-muted">Сумма заказов</div>
        <div class="h4 mb-0">
          <?= number_format((float)$stats['total_sum'], 2, ',', ' ') ?> ₽
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="small text-muted">СБП / Оплата при получении</div>
        <div class="h5 mb-0">
          <?= (int)$stats['sbp_count'] ?> / <?= (int)$stats['cod_count'] ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="small text-muted">Оплачено / Ожидает оплаты</div>
        <div class="h5 mb-0">
          <?= (int)$stats['paid_count'] ?> / <?= (int)$stats['pending_count'] ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Фильтры -->
<form method="get" action="/admin/orders" class="card mb-4">
  <div class="card-body">
    <div class="row g-3 align-items-end">
      <div class="col-md-3">
        <label for="f-date-from" class="form-label">Дата с</label>
        <input
          type="date"
          id="f-date-from"
          name="date_from"
          value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES) ?>"
          class="form-control"
        >
      </div>
      <div class="col-md-3">
        <label for="f-date-to" class="form-label">Дата по</label>
        <input
          type="date"
          id="f-date-to"
          name="date_to"
          value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES) ?>"
          class="form-control"
        >
      </div>
      <div class="col-md-2">
        <label for="f-payment-method" class="form-label">Способ оплаты</label>
        <select
          id="f-payment-method"
          name="payment_method"
          class="form-select"
        >
          <option value="">Все</option>
          <option value="cod"
            <?= ($filters['payment_method'] ?? '') === 'cod' ? 'selected' : '' ?>>
            Оплата при получении
          </option>
          <option value="sbp"
            <?= ($filters['payment_method'] ?? '') === 'sbp' ? 'selected' : '' ?>>
            СБП (онлайн)
          </option>
        </select>
      </div>
      <div class="col-md-2">
        <label for="f-payment-status" class="form-label">Статус оплаты</label>
        <select
          id="f-payment-status"
          name="payment_status"
          class="form-select"
        >
          <option value="">Все</option>
          <option value="pending"
            <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>
            Ожидает оплаты
          </option>
          <option value="paid"
            <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>
            Оплачен
          </option>
          <option value="failed"
            <?= ($filters['payment_status'] ?? '') === 'failed' ? 'selected' : '' ?>>
            Ошибка оплаты
          </option>
          <option value="cancelled"
            <?= ($filters['payment_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>
            Отменён
          </option>
          <option value="expired"
            <?= ($filters['payment_status'] ?? '') === 'expired' ? 'selected' : '' ?>>
            Истёк
          </option>
        </select>
      </div>
      <div class="col-md-2">
        <label for="f-order-status" class="form-label">Статус заказа</label>
        <select
          id="f-order-status"
          name="order_status"
          class="form-select"
        >
          <option value="">Все</option>
          <option value="new"
            <?= ($filters['order_status'] ?? '') === 'new' ? 'selected' : '' ?>>
            Новый
          </option>
          <option value="processing"
            <?= ($filters['order_status'] ?? '') === 'processing' ? 'selected' : '' ?>>
            В обработке
          </option>
          <option value="shipped"
            <?= ($filters['order_status'] ?? '') === 'shipped' ? 'selected' : '' ?>>
            Отправлен
          </option>
          <option value="delivered"
            <?= ($filters['order_status'] ?? '') === 'delivered' ? 'selected' : '' ?>>
            Доставлен
          </option>
          <option value="cancelled"
            <?= ($filters['order_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>
            Отменён
          </option>
        </select>
      </div>
    </div>

    <div class="mt-3">
      <button type="submit" class="btn btn-primary me-2">Применить</button>
      <a href="/admin/orders" class="btn btn-outline-secondary">Сбросить</a>
    </div>
  </div>
</form>

<!-- Активные заказы -->
<h3 class="mt-4">Управление заказами</h3>

<?php if (empty($activeOrders)): ?>
  <p class="text-muted">Нет активных заказов по текущему фильтру.</p>
<?php else: ?>
  <table class="table table-hover align-middle mt-2">
    <thead>
      <tr>
        <th>ID</th>
        <th>Дата</th>
        <th>Клиент</th>
        <th>Статус заказа</th>
        <th>Оплата</th>
        <th>Сумма</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($activeOrders as $o): ?>
        <tr class="<?= adminOrderRowClass(
              $o['payment_method']  ?? null,
              $o['payment_status']  ?? null,
              $o['status']          ?? null
            ) ?>">
          <td><?= (int)$o['id'] ?></td>
          <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($o['created_at']))) ?></td>
          <td><?= htmlspecialchars($o['client_name'] ?? '', ENT_QUOTES) ?></td>
          <td>
            <form action="/admin/orders/<?= (int)$o['id'] ?>/status"
                  method="post"
                  class="d-flex">
              <select name="status" class="form-select form-select-sm me-2">
                <?php foreach ([
                  'new'        => 'Новый',
                  'processing' => 'В обработке',
                  'shipped'    => 'Отправлен',
                  'delivered'  => 'Доставлен',
                  'cancelled'  => 'Отменён',
                ] as $key => $label): ?>
                  <option value="<?= $key ?>"
                    <?= ($o['status'] ?? '') === $key ? 'selected' : '' ?>>
                    <?= $label ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-primary">OK</button>
            </form>
          </td>
          <td>
            <div>
              <div class="small text-muted">
                <?= paymentMethodLabel($o['payment_method'] ?? 'cod') ?>
              </div>
              <div>
                <?= paymentStatusBadgeHtml(
                    $o['payment_method'] ?? 'cod',
                    $o['payment_status'] ?? 'pending'
                ) ?>
              </div>
            </div>
          </td>
          <td><?= number_format((float)$o['total'], 2, ',', ' ') ?> ₽</td>
          <td>
            <a href="/orders/<?= (int)$o['id'] ?>"
               class="btn btn-sm btn-outline-secondary">
              Просмотр
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<!-- История заказов -->
<h3 class="mt-5">История заказов</h3>

<?php if (empty($historyOrders)): ?>
  <p class="text-muted">Нет архивных заказов по текущему фильтру.</p>
<?php else: ?>
  <table class="table table-hover align-middle mt-2">
    <thead>
      <tr>
        <th>ID</th>
        <th>Дата</th>
        <th>Клиент</th>
        <th>Статус заказа</th>
        <th>Оплата</th>
        <th>Сумма</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($historyOrders as $o): ?>
        <tr class="<?= adminOrderRowClass(
              $o['payment_method']  ?? null,
              $o['payment_status']  ?? null,
              $o['status']          ?? null
            ) ?>">
          <td><?= (int)$o['id'] ?></td>
          <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($o['created_at']))) ?></td>
          <td><?= htmlspecialchars($o['client_name'] ?? '', ENT_QUOTES) ?></td>
          <td>
            <form action="/admin/orders/<?= (int)$o['id'] ?>/status"
                  method="post"
                  class="d-flex">
              <select name="status" class="form-select form-select-sm me-2">
                <?php foreach ([
                  'new'        => 'Новый',
                  'processing' => 'В обработке',
                  'shipped'    => 'Отправлен',
                  'delivered'  => 'Доставлен',
                  'cancelled'  => 'Отменён',
                ] as $key => $label): ?>
                  <option value="<?= $key ?>"
                    <?= ($o['status'] ?? '') === $key ? 'selected' : '' ?>>
                    <?= $label ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-primary">OK</button>
            </form>
          </td>
          <td>
            <div>
              <div class="small text-muted">
                <?= paymentMethodLabel($o['payment_method'] ?? 'cod') ?>
              </div>
              <div>
                <?= paymentStatusBadgeHtml(
                    $o['payment_method'] ?? 'cod',
                    $o['payment_status'] ?? 'pending'
                ) ?>
              </div>
            </div>
          </td>
          <td><?= number_format((float)$o['total'], 2, ',', ' ') ?> ₽</td>
          <td>
            <a href="/orders/<?= (int)$o['id'] ?>"
               class="btn btn-sm btn-outline-secondary">
              Просмотр
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
