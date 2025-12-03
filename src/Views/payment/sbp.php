<?php
// src/Views/payment/sbp.php
/** @var array $order */
/** @var array $payment */

use App\Services\Payment\SbpMockGateway;

require __DIR__ . '/../layout/header.php';

// Формируем строку для QR (демо-URI СБП)
$qrText        = 'sbp://sandbox/' . ($payment['external_id'] ?? '');
$currentStatus = $payment['status'] ?? 'pending';

// TTL: считаем от created_at
$expiresInSeconds = SbpMockGateway::TTL_SECONDS; // базовое значение по умолчанию

if (!empty($payment['created_at'])) {
    $createdTs = strtotime($payment['created_at']);
    $expiresTs = $createdTs + SbpMockGateway::TTL_SECONDS;
    $nowTs     = time();

    $expiresInSeconds = max(0, $expiresTs - $nowTs);

    // Если уже истёк, а в БД ещё pending — показываем как expired
    if ($currentStatus === 'pending' && $expiresInSeconds === 0) {
        $currentStatus = 'expired';
    }
}

?>

<div class="container mt-4">
    <h1 class="mb-3">Оплата по СБП (демо)</h1>

    <p>
        Заказ №<?= htmlspecialchars($order['id']) ?>
        на сумму
        <strong><?= number_format($payment['amount'], 2, ',', ' ') ?> ₽</strong>
    </p>

    <p>
        Текущий статус платежа:
        <strong
            id="payment-status-label"
            data-status="<?= htmlspecialchars($currentStatus, ENT_QUOTES) ?>"
        >
            <?= htmlspecialchars($currentStatus) ?>
        </strong>
    </p>

    <?php if (!in_array($currentStatus, ['paid', 'cancelled', 'failed'], true)): ?>
        <?php
          $totalTtl   = SbpMockGateway::TTL_SECONDS;
          $percentage = $totalTtl > 0
              ? (int)max(0, min(100, round($expiresInSeconds / $totalTtl * 100)))
              : 0;
        ?>
        <div class="mt-2 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Время до истечения QR</span>
                <span class="badge bg-warning text-dark">
                    Осталось:
                    <span
                        id="qr-timer"
                        data-seconds="<?= (int)$expiresInSeconds ?>"
                        data-total="<?= (int)$totalTtl ?>"
                    >
                        <?= gmdate('i:s', $expiresInSeconds) ?>
                    </span>
                </span>
            </div>
            <div class="progress mt-2" style="height: 6px;">
                <div
                    id="qr-progress"
                    class="progress-bar bg-warning"
                    role="progressbar"
                    style="width: <?= $percentage ?>%;"
                    aria-valuenow="<?= $percentage ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                ></div>
            </div>
        </div>
    <?php endif; ?>


    <p id="payment-status-hint" class="text-muted">
        <?php if ($currentStatus === 'pending'): ?>
            Ожидаем подтверждения оплаты. Статус и таймер обновляются автоматически.
        <?php elseif ($currentStatus === 'paid'): ?>
            Оплата уже получена. Можете перейти к деталям заказа.
        <?php elseif ($currentStatus === 'expired'): ?>
            Срок действия QR-кода истёк. Сгенерируйте новый QR.
        <?php endif; ?>
    </p>

    <div class="alert alert-info">
        Это демонстрационный режим СБП. Реальных списаний не происходит.
    </div>

    <div class="row mb-4">
        <div class="col-md-6 text-center">
            <h5>QR-код для оплаты</h5>
            <div id="sbp-qr"
                 class="d-inline-block p-3 bg-white border rounded">
                <!-- Сюда JS нарисует QR -->
            </div>
        </div>
        <div class="col-md-6">
            <h6>Техническая строка (для отладки)</h6>
            <pre class="bg-light p-3 border rounded small mb-0">
<?= htmlspecialchars($qrText) ?>
            </pre>
        </div>
    </div>

    <form
        id="simulate-form"
        method="post"
        action="/payment/sbp/<?= (int)$order['id'] ?>/simulate-success"
        class="d-inline"
    >
        <button id="simulate-button" type="submit" class="btn btn-success">
            Симулировать успешную оплату
        </button>
    </form>

    <form
        id="restart-form"
        method="post"
        action="/payment/sbp/<?= (int)$order['id'] ?>/restart"
        class="d-inline ms-2"
    >
        <button
            type="submit"
            class="btn btn-outline-primary"
            id="restart-button"
            <?= $currentStatus === 'expired' ? '' : 'style="display:none"' ?>
        >
            Сгенерировать новый QR
        </button>
    </form>

    <a href="/orders/<?= (int)$order['id'] ?>" class="btn btn-secondary ms-2">
        Вернуться к заказу
    </a>
</div>

<!-- Подключаем библиотеку генерации QR-кода -->
<script src="/js/qrcodejs-master/qrcode.min.js"></script>
<script>
  (function () {
    // --- QR-код ---
    var qrContainer = document.getElementById('sbp-qr');
    if (qrContainer && typeof QRCode !== 'undefined') {
      var qrText = "<?= htmlspecialchars($qrText, ENT_QUOTES) ?>";
      new QRCode(qrContainer, {
        text: qrText,
        width: 256,
        height: 256,
        correctLevel: QRCode.CorrectLevel.M
      });
    }

    // --- Поллинг статуса и TTL ---
    var statusLabel = document.getElementById('payment-status-label');
    var statusHint  = document.getElementById('payment-status-hint');
    var simulateBtn = document.getElementById('simulate-button');
    var restartBtn  = document.getElementById('restart-button');
    var timerEl     = document.getElementById('qr-timer');
    var progressEl  = document.getElementById('qr-progress');
    var orderId     = <?= (int)$order['id'] ?>;

    if (!statusLabel) {
      return;
    }

    var statusTitles = {
      pending:   'Ожидает оплаты',
      paid:      'Оплачен',
      failed:    'Ошибка оплаты',
      cancelled: 'Отменён',
      expired:   'Истёк'
    };

    var countdownTimer = null;
    var pollTimer      = null;

    function applyStatus(status) {
      statusLabel.dataset.status = status;
      statusLabel.textContent = statusTitles[status] || status;

      if (statusHint) {
        if (status === 'pending') {
          statusHint.textContent = 'Ожидаем подтверждения оплаты. Статус и таймер обновляются автоматически.';
        } else if (status === 'paid') {
          statusHint.textContent = 'Оплата успешно получена. Можете перейти к деталям заказа.';
        } else if (status === 'expired') {
          statusHint.textContent = 'Срок действия QR-кода истёк. Сгенерируйте новый QR-код.';
        } else if (status === 'failed') {
          statusHint.textContent = 'Произошла ошибка при обработке оплаты.';
        } else if (status === 'cancelled') {
          statusHint.textContent = 'Платёж был отменён.';
        } else {
          statusHint.textContent = '';
        }
      }

      if (simulateBtn) {
        if (status === 'pending') {
          simulateBtn.disabled = false;
          simulateBtn.classList.remove('disabled');
        } else {
          simulateBtn.disabled = true;
          simulateBtn.classList.add('disabled');
        }
      }

      if (restartBtn) {
        if (status === 'expired') {
          restartBtn.style.display = '';
        } else {
          restartBtn.style.display = 'none';
        }
      }

      if (status !== 'pending' && countdownTimer) {
        clearInterval(countdownTimer);
        countdownTimer = null;
      }
    }

    function startCountdown(initialSeconds) {
      if ((!timerEl && !progressEl) || initialSeconds <= 0) {
        return;
      }

      var remaining = initialSeconds;

      // Берём общий TTL либо из data-total, либо из initialSeconds
      var total = initialSeconds;
      if (timerEl && timerEl.dataset.total) {
        var parsedTotal = parseInt(timerEl.dataset.total, 10);
        if (!isNaN(parsedTotal) && parsedTotal > 0) {
          total = parsedTotal;
        }
      }

      function updateTimer() {
        if (timerEl) {
          var m  = Math.floor(remaining / 60);
          var s  = remaining % 60;
          var mm = (m < 10 ? '0' : '') + m;
          var ss = (s < 10 ? '0' : '') + s;
          timerEl.textContent = mm + ':' + ss;
        }

        if (progressEl) {
          var percent = total > 0 ? (remaining / total) * 100 : 0;
          if (percent < 0) percent = 0;
          if (percent > 100) percent = 100;
          progressEl.style.width = percent + '%';
          progressEl.setAttribute('aria-valuenow', Math.round(percent));
        }
      }

      updateTimer();

      countdownTimer = setInterval(function () {
        remaining -= 1;
        if (remaining <= 0) {
          remaining = 0;
          updateTimer();
          clearInterval(countdownTimer);
          countdownTimer = null;

          var current = statusLabel.dataset.status || 'pending';
          if (current === 'pending') {
            // Локально считаем, что платёж истёк
            applyStatus('expired');
          }
          return;
        }
        updateTimer();
      }, 1000);
    }

    function pollStatus() {
      var current = statusLabel.dataset.status || 'pending';
      if (current !== 'pending') {
        return;
      }

      fetch('/payment/sbp/' + orderId + '/status-json', {
        headers: {
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('HTTP ' + response.status);
          }
          return response.json();
        })
        .then(function (data) {
          if (data && data.status) {
            var newStatus = data.status;
            if (newStatus !== current) {
              applyStatus(newStatus);
            }
            if (newStatus !== 'pending') {
              if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
              }
            }
          }
        })
        .catch(function () {
          // Ошибки поллинга можно тихо игнорировать
        });
    }

    var initialStatus = statusLabel.dataset.status || 'pending';
    applyStatus(initialStatus);

    if (timerEl) {
      var initialSeconds = parseInt(timerEl.dataset.seconds || '0', 10);
      if (!isNaN(initialSeconds) &&
          initialSeconds > 0 &&
          initialStatus === 'pending') {
        startCountdown(initialSeconds);
      }
    }

    pollTimer = setInterval(pollStatus, 5000);
  })();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
