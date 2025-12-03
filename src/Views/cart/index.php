<?php
// src/Views/cart/index.php
$title = 'Ваша корзина';
require __DIR__ . '/../layout/header.php';

// Контроллер передаёт $items и $totalPrice
$items      = $items      ?? [];
$totalPrice = $totalPrice ?? 0.0;
?>

<h2 class="mb-4">Корзина</h2>

<?php if (empty($items)): ?>
  <div class="profile-card p-4">
    <p class="mb-2">
      Корзина пуста.
    </p>
    <a href="/products" class="btn btn-primary btn-sm">
      Перейти в каталог
    </a>
  </div>
<?php else: ?>
  <div class="row g-4">
    <!-- Товары в корзине -->
    <div class="col-lg-8">
      <div class="profile-card p-4 h-100">
        <h4 class="mb-3">Товары в корзине</h4>

        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>Товар</th>
                <th class="text-end">Цена</th>
                <th class="text-center">Количество</th>
                <th class="text-end">Сумма</th>
                <th class="text-center" style="width: 80px;"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
                <tr>
                  <td><?= htmlspecialchars($item['name']) ?></td>
                  <td class="text-end">
                    <?= number_format($item['price'], 2, ',', ' ') ?> ₽
                  </td>
                  <td class="text-center">
                    <?= (int)$item['quantity'] ?>
                  </td>
                  <td class="text-end">
                    <?= number_format($item['subtotal'], 2, ',', ' ') ?> ₽
                  </td>
                  <td class="text-center">
                    <form
                      action="/cart/remove/<?= (int)$item['id'] ?>"
                      method="post"
                      class="d-inline"
                    >
                      <button class="btn btn-sm btn-outline-danger" title="Удалить из корзины">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <tr>
                <th colspan="3" class="text-end">Итого:</th>
                <th class="text-end">
                  <?= number_format($totalPrice, 2, ',', ' ') ?> ₽
                </th>
                <th></th>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Итог и действия -->
    <div class="col-lg-4">
      <div class="profile-card p-4 h-100 d-flex flex-column">
        <h4 class="mb-3">Итого по заказу</h4>

        <p class="mb-1">
          <strong>Сумма товаров:</strong>
          <?= number_format($totalPrice, 2, ',', ' ') ?> ₽
        </p>
        <p class="text-muted small">
          Скидки по промокодам можно будет применить на шаге оформления заказа.
        </p>

        <div class="mt-auto d-grid gap-2">
          <a href="/checkout" class="btn btn-primary">
            Оформить заказ
          </a>
          <a href="/products" class="btn btn-outline-secondary">
            Продолжить покупки
          </a>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
