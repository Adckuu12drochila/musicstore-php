<?php
$title = 'Оформление заказа';
require __DIR__ . '/../layout/header.php';

// Эти переменные заполняет OrderController::processOrder()
// при действии "apply_coupon" или при ошибках.
// При первом GET-запросе они будут null.
$summarySubtotal = $summarySubtotal ?? null;
$summaryDiscount = $summaryDiscount ?? null;
$summaryTotal    = $summaryTotal ?? null;

// Запоминаем выбранный способ оплаты, чтобы не сбрасывался после POST
$selectedMethod = $_POST['payment_method'] ?? 'sbp';
if (!in_array($selectedMethod, ['sbp', 'cod'], true)) {
    $selectedMethod = 'sbp';
}
?>

<h2 class="mb-4">Оформление заказа</h2>

<form action="/checkout" method="post">
  <div class="row g-4">
    <!-- Левая колонка: данные получателя и адрес -->
    <div class="col-lg-7">
      <div class="profile-card p-4 h-100">
        <h4 class="mb-3">Данные получателя</h4>

        <div class="mb-3">
          <label class="form-label">Имя получателя</label>
          <input
            type="text"
            name="client_name"
            class="form-control"
            value="<?= htmlspecialchars($_POST['client_name'] ?? '') ?>"
            required
          >
        </div>

        <div class="mb-3">
          <label class="form-label">Телефон</label>
          <input
            type="text"
            name="phone"
            class="form-control"
            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
            required
          >
        </div>

        <div class="mb-0">
          <label class="form-label">Адрес доставки</label>
          <textarea
            name="delivery_address"
            class="form-control"
            rows="3"
            required
          ><?= htmlspecialchars($_POST['delivery_address'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Правая колонка: промокод, оплата, итог -->
    <div class="col-lg-5">
      <div class="profile-card p-4 h-100 d-flex flex-column">
        <h4 class="mb-3">Оплата и итог</h4>

        <!-- Промокод -->
        <div class="mb-3">
          <label for="coupon_code" class="form-label">Промокод</label>
          <input
            type="text"
            name="coupon_code"
            id="coupon_code"
            value="<?= htmlspecialchars($_POST['coupon_code'] ?? '', ENT_QUOTES) ?>"
            class="form-control"
            placeholder="Если есть промокод, введите его здесь"
          >
          <div class="form-text">
            Скидка будет применена при применении промокода или оформлении заказа.
          </div>
        </div>

        <!-- Способ оплаты -->
        <div class="mb-3">
          <label class="form-label d-block">Способ оплаты</label>

          <div class="form-check">
            <input
              class="form-check-input"
              type="radio"
              name="payment_method"
              id="payment_method_sbp"
              value="sbp"
              <?= $selectedMethod === 'sbp' ? 'checked' : '' ?>
            >
            <label class="form-check-label" for="payment_method_sbp">
              Оплата по СБП (демо)
            </label>
          </div>

          <div class="form-check">
            <input
              class="form-check-input"
              type="radio"
              name="payment_method"
              id="payment_method_cod"
              value="cod"
              <?= $selectedMethod === 'cod' ? 'checked' : '' ?>
            >
            <label class="form-check-label" for="payment_method_cod">
              Оплата при получении
            </label>
          </div>

          <small class="text-muted">
            В режиме демо СБП реальные списания не выполняются.
          </small>
        </div>

        <!-- Итог по заказу -->
        <div class="mt-auto">
          <div class="border rounded-3 p-3 bg-light-subtle">
            <h6 class="mb-2">Итог по заказу</h6>

            <?php if ($summarySubtotal !== null): ?>
              <p class="mb-1">
                <strong>Сумма по товарам:</strong>
                <?= number_format($summarySubtotal, 2, ',', ' ') ?> ₽
              </p>

              <?php if ($summaryDiscount > 0): ?>
                <p class="mb-1">
                  <strong>Скидка по промокоду:</strong>
                  − <?= number_format($summaryDiscount, 2, ',', ' ') ?> ₽
                </p>
                <p class="mb-0">
                  <strong>Итого к оплате:</strong>
                  <?= number_format($summaryTotal, 2, ',', ' ') ?> ₽
                </p>
              <?php else: ?>
                <p class="mb-0">
                  <strong>Итого к оплате:</strong>
                  <?= number_format($summarySubtotal, 2, ',', ' ') ?> ₽
                </p>
              <?php endif; ?>

            <?php else: ?>
              <p class="text-muted mb-0">
                Итоговая сумма будет рассчитана при применении промокода или оформлении заказа.
              </p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Кнопки действий -->
  <div class="mt-3 d-flex flex-wrap gap-2 justify-content-between">
    <div class="d-flex flex-wrap gap-2">
      <button
        type="submit"
        name="action"
        value="apply_coupon"
        class="btn btn-outline-primary"
      >
        Применить промокод
      </button>

      <button
        type="submit"
        name="action"
        value="place_order"
        class="btn btn-primary"
      >
        Оформить заказ
      </button>
    </div>

    <a href="/cart" class="btn btn-secondary">
      Назад в корзину
    </a>
  </div>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
