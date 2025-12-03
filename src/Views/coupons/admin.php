<?php
// src/Views/coupons/admin.php

$title = 'Купоны / промокоды';
require __DIR__ . '/../layout/header.php';

$coupons = $coupons ?? [];
?>

<h2 class="mb-4">Купоны / промокоды</h2>

<div class="row g-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Создать новый купон</h5>
        <form method="post" action="/admin/coupons">
          <div class="mb-3">
            <label for="coupon-code" class="form-label">Код купона</label>
            <input
              type="text"
              name="code"
              id="coupon-code"
              class="form-control"
              required
              placeholder="Например, WELCOME10"
            >
          </div>

          <div class="mb-3">
            <label for="coupon-percent" class="form-label">Скидка, %</label>
            <input
              type="number"
              name="discount_percent"
              id="coupon-percent"
              class="form-control"
              min="1"
              max="100"
              step="0.01"
              required
            >
          </div>

          <div class="mb-3">
            <label for="coupon-expires" class="form-label">Действителен до (необязательно)</label>
            <input
              type="date"
              name="expires_at"
              id="coupon-expires"
              class="form-control"
            >
            <div class="form-text">
              Если дата не указана, купон будет бессрочным (пока не отключите).
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            Создать купон
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Список купонов</h5>

        <?php if (empty($coupons)): ?>
          <p class="text-muted">Купонов пока нет.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Код</th>
                  <th>Скидка</th>
                  <th>Статус</th>
                  <th>Действителен до</th>
                  <th>Действия</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($coupons as $c): ?>
                  <tr>
                    <td><?= (int)$c['id'] ?></td>
                    <td><code><?= htmlspecialchars($c['code'], ENT_QUOTES) ?></code></td>
                    <td><?= number_format((float)$c['discount_percent'], 2, ',', ' ') ?>%</td>
                    <td>
                      <?php if (!empty($c['is_active'])): ?>
                        <span class="badge bg-success">Активен</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Выключен</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?=
                        !empty($c['expires_at'])
                          ? htmlspecialchars(date('d.m.Y', strtotime($c['expires_at'])))
                          : '—'
                      ?>
                    </td>
                    <td>
                      <form method="post"
                            action="/admin/coupons/<?= (int)$c['id'] ?>/toggle"
                            onsubmit="return confirm('Изменить статус купона?');">
                        <button type="submit"
                                class="btn btn-sm <?= !empty($c['is_active']) ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                          <?= !empty($c['is_active']) ? 'Выключить' : 'Включить' ?>
                        </button>
                      </form>
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
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
