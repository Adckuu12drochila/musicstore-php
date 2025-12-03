<?php
// src/Controllers/OrderController.php

namespace App\Controllers;

use PDO;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Helpers\Flash;
use App\Services\Mailer;
use App\Models\SbpPayment;
use App\Models\Coupon;

class OrderController
{
    private Order     $orderModel;
    private OrderItem $itemModel;
    private Product   $productModel;
    private array     $mailConfig;
    private SbpPayment $sbpPaymentModel;
    private Coupon $couponModel;

    public function __construct(PDO $pdo)
    {
        $this->orderModel   = new Order($pdo);
        $this->itemModel    = new OrderItem($pdo);
        $this->productModel = new Product($pdo);
        $this->sbpPaymentModel = new SbpPayment($pdo);
        $this->couponModel    = new Coupon($pdo);

        // Загружаем конфиг почты
        $config = require __DIR__ . '/../../config.php';
        $this->mailConfig = $config['mail'];

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /** Показывает форму оформления заказа */
    public function showCheckout(): void
    {
        require __DIR__ . '/../Views/orders/checkout.php';
    }

    /** Обрабатывает отправку заказа */
    public function processOrder(): void
    {
        $action = $_POST['action'] ?? 'place_order';

        $paymentMethod = $_POST['payment_method'] ?? 'cod';
        $paymentMethod = $paymentMethod === 'sbp' ? 'sbp' : 'cod';

        $data = [
            'user_id'          => $_SESSION['user_id'] ?? null,
            'client_name'      => trim($_POST['client_name'] ?? ''),
            'phone'            => trim($_POST['phone'] ?? ''),
            'delivery_address' => trim($_POST['delivery_address'] ?? ''),
            'payment_method'   => $paymentMethod,
            'payment_status'   => 'pending',
        ];

        $couponCode     = trim($_POST['coupon_code'] ?? '');
        $coupon         = null;
        $discountAmount = 0.0;
        $cartItems      = [];
        $cartSubtotal   = 0.0;
        $errors         = [];

        // 1) Собираем корзину
        if (empty($_SESSION['cart'])) {
            $errors[] = 'Корзина пуста.';
        } else {
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $prod = $this->productModel->find($productId);
                if (!$prod) {
                    continue;
                }
                $quantity = (int)$quantity;
                if ($quantity <= 0) {
                    continue;
                }

                $cartItems[] = [
                    'product_id' => (int)$productId,
                    'quantity'   => $quantity,
                    'unit_price' => (float)$prod['price'],
                    'name'       => $prod['name'] ?? '',
                ];

                $cartSubtotal += (float)$prod['price'] * $quantity;
            }

            if (empty($cartItems)) {
                $errors[] = 'Корзина пуста или товары недоступны.';
            }
        }

        // 2) Считаем скидку по промокоду (если он есть и корзина ок)
        if ($couponCode !== '' && empty($errors)) {
            $coupon = $this->couponModel->findActiveByCode($couponCode);
            if (!$coupon) {
                $errors[] = 'Промокод недействителен или истёк.';
            } else {
                $percent       = (float)$coupon['discount_percent'];
                $discountAmount = round($cartSubtotal * ($percent / 100), 2);
            }
        }

        $finalTotal = max(0, $cartSubtotal - $discountAmount);

        // 3) Если нажали "Применить промокод" — НИЧЕГО не создаём, только показываем чекаут с суммами
        if ($action === 'apply_coupon') {
            if ($errors) {
                foreach ($errors as $e) {
                    Flash::set('error', $e);
                }
            } else {
                if ($discountAmount > 0) {
                    Flash::set(
                        'success',
                        'Промокод применён. Скидка ' .
                        number_format($discountAmount, 2, ',', ' ') . ' ₽.'
                    );
                } else {
                    Flash::set('info', 'Промокод не изменяет итоговую сумму.');
                }
            }

            // Передаём суммы во вьюху
            $summarySubtotal = $cartSubtotal;
            $summaryDiscount = $discountAmount;
            $summaryTotal    = $finalTotal;

            require __DIR__ . '/../Views/orders/checkout.php';
            return;
        }

        // 4) Полная валидация для "Оформить заказ"
        if ($data['client_name'] === '')      $errors[] = 'Укажите имя получателя.';
        if ($data['phone'] === '')            $errors[] = 'Укажите телефон.';
        if ($data['delivery_address'] === '') $errors[] = 'Укажите адрес доставки.';

        if ($errors) {
            foreach ($errors as $e) {
                Flash::set('error', $e);
            }

            // Чтобы человек всё равно видел итог по промокоду
            $summarySubtotal = $cartSubtotal;
            $summaryDiscount = $discountAmount;
            $summaryTotal    = $finalTotal;

            require __DIR__ . '/../Views/orders/checkout.php';
            return;
        }

        // 5) Создаём заказ
        $orderData = $data;
        $orderData['total_amount']    = $finalTotal;
        $orderData['coupon_code']     = $couponCode !== '' ? $couponCode : null;
        $orderData['discount_amount'] = $discountAmount;

        $orderId = $this->orderModel->create($orderData);

        // 6) Сохраняем позиции
        $itemsToInsert = [];
        foreach ($cartItems as $ci) {
            $itemsToInsert[] = [
                'product_id' => $ci['product_id'],
                'quantity'   => $ci['quantity'],
                'unit_price' => $ci['unit_price'],
            ];
        }
        $this->itemModel->createBatch($orderId, $itemsToInsert);

        // 7) Очищаем корзину
        $_SESSION['cart'] = [];

        // 8) Загружаем заказ для писем
        $order = $this->orderModel->findWithItems($orderId);
        if ($order) {
            $order['subtotal']        = $cartSubtotal;
            $order['discount_amount'] = $discountAmount;
            $order['total']           = $finalTotal;
            $order['coupon_code']     = $couponCode !== '' ? $couponCode : null;
        }

        // 9) Письма
        $mailer = new Mailer($this->mailConfig);
        if ($order && !empty($order['user_email'])) {
            $mailer->sendOrderConfirmation(
                $order['user_email'],
                $order['client_name'],
                $order
            );
        }
        if ($order) {
            $mailer->sendNewOrderNotification(
                $this->mailConfig['admin_email'],
                $order
            );
        }

        // 10) Редирект по способу оплаты
        if ($paymentMethod === 'sbp') {
            Flash::set(
                'info',
                'Заказ создан. Теперь оплатите его по СБП.'
            );
            header('Location: /payment/sbp/' . $orderId);
        } else {
            Flash::set(
                'success',
                'Заказ оформлен. Спасибо за покупку! Мы отправили вам письмо с подтверждением.'
            );
            header('Location: /orders');
        }
        exit;
    }

    /** История заказов текущего пользователя */
    public function list(): void
    {
        $userId = $_SESSION['user_id'] ?? 0;
        $orders = $this->orderModel->findByUser($userId);

        // Считаем сумму каждого заказа (или берём total_amount, если он есть)
        foreach ($orders as &$order) {
            if (isset($order['total_amount']) && $order['total_amount'] > 0) {
                $order['total'] = (float)$order['total_amount'];
                continue;
            }

            $items = $this->itemModel->findByOrder((int)$order['id']);
            $sum = 0;
            foreach ($items as $it) {
                $sum += $it['unit_price'] * $it['quantity'];
            }
            $order['total'] = $sum;
        }
        unset($order);

        require __DIR__ . '/../Views/orders/index.php';
    }


    /** Детали заказа */
    public function view(int $id): void
    {
        $order = $this->orderModel->findWithItems($id);
        if (!$order) {
            http_response_code(404);
            echo 'Заказ не найден';
            return;
        }

        // Позиции заказа
        $items = $order['items'] ?? [];
        $total = 0;

        // Платежи по СБП для истории (только если способ оплаты — СБП)
        $sbpPayments = [];
        if (($order['payment_method'] ?? 'cod') === 'sbp') {
            $sbpPayments = $this->sbpPaymentModel->findByOrder($id);
        }

        require __DIR__ . '/../Views/orders/view.php';
    }

    /** Админ: список всех заказов (разделение на активные и историю) */
    public function adminList(): void
    {
        // 1. Читаем фильтры из GET
        $filters = [
            'date_from'      => $_GET['date_from']      ?? '',
            'date_to'        => $_GET['date_to']        ?? '',
            'payment_method' => $_GET['payment_method'] ?? '',
            'payment_status' => $_GET['payment_status'] ?? '',
            'order_status'   => $_GET['order_status']   ?? '',
        ];

        // 2. Берём все заказы
        $orders = $this->orderModel->all();

        // 3. Подсчитываем сумму по каждому заказу
        foreach ($orders as &$o) {
            // 3.1) Сумма по товарам (subtotal)
            $items = $this->itemModel->findByOrder((int)$o['id']);
            $subtotal = 0.0;
            foreach ($items as $it) {
                $subtotal += $it['unit_price'] * $it['quantity'];
            }
            $o['subtotal'] = $subtotal;

            // 3.2) Скидка
            $discount = isset($o['discount_amount'])
                ? (float)$o['discount_amount']
                : 0.0;

            // 3.3) Итоговая сумма:
            //    - если в БД уже сохранён total_amount, берём его;
            //    - иначе считаем "subtotal - discount" (для старых заказов без купонов).
            if (isset($o['total_amount']) && $o['total_amount'] !== null && (float)$o['total_amount'] > 0) {
                $total = (float)$o['total_amount'];
            } else {
                $total = max(0, $subtotal - $discount);
            }

            $o['total']     = $total;    // эту сумму используем в таблице и статистике
            $o['discount']  = $discount; // опционально: можно показывать и скидку в админке
        }
        unset($o);

        // 4. Применяем фильтры
        $filtered = [];
        $fromTs = null;
        $toTs   = null;

        if ($filters['date_from'] !== '') {
            $fromTs = strtotime($filters['date_from'] . ' 00:00:00');
        }
        if ($filters['date_to'] !== '') {
            $toTs = strtotime($filters['date_to'] . ' 23:59:59');
        }

        foreach ($orders as $o) {
            // Фильтр по дате
            if ($fromTs !== null || $toTs !== null) {
                $createdAt = $o['created_at'] ?? null;
                if ($createdAt) {
                    $createdTs = strtotime($createdAt);
                } else {
                    // если вдруг нет даты — считаем, что он "не подходит" под строгий фильтр
                    $createdTs = null;
                }

                if ($fromTs !== null && ($createdTs === null || $createdTs < $fromTs)) {
                    continue;
                }
                if ($toTs !== null && ($createdTs === null || $createdTs > $toTs)) {
                    continue;
                }
            }

            // Фильтр по способу оплаты
            $pm = $o['payment_method'] ?? 'cod';
            if ($filters['payment_method'] !== '' && $pm !== $filters['payment_method']) {
                continue;
            }

            // Фильтр по статусу оплаты
            $ps = $o['payment_status'] ?? 'pending';
            if ($filters['payment_status'] !== '' && $ps !== $filters['payment_status']) {
                continue;
            }

            // Фильтр по статусу заказа
            $os = $o['status'] ?? 'new';
            if ($filters['order_status'] !== '' && $os !== $filters['order_status']) {
                continue;
            }

            $filtered[] = $o;
        }

        // 5. Статистика для дашборда (по отфильтрованным заказам)
        $stats = [
            'total_count'   => 0,
            'total_sum'     => 0.0,
            'cod_count'     => 0,
            'sbp_count'     => 0,
            'paid_count'    => 0,
            'pending_count' => 0,
        ];

        foreach ($filtered as $o) {
            $stats['total_count']++;
            $stats['total_sum'] += (float)($o['total'] ?? 0);

            $pm = $o['payment_method'] ?? 'cod';
            $ps = $o['payment_status'] ?? 'pending';

            if ($pm === 'cod') {
                $stats['cod_count']++;
            } elseif ($pm === 'sbp') {
                $stats['sbp_count']++;
            }

            if ($ps === 'paid') {
                $stats['paid_count']++;
            } elseif ($ps === 'pending') {
                $stats['pending_count']++;
            }
        }

        // 6. Делим отфильтрованные заказы на "активные" и "история"
        $activeOrders  = [];
        $historyOrders = [];

        foreach ($filtered as $o) {
            $os = $o['status'] ?? 'new';
            if (in_array($os, ['new', 'processing', 'shipped'], true)) {
                $activeOrders[] = $o;
            } else {
                $historyOrders[] = $o;
            }
        }

        require __DIR__ . '/../Views/orders/admin.php';
    }

    /** Админ: обновление статуса заказа */
    public function updateStatus(int $id): void
    {
        $status = $_POST['status'] ?? '';
        $valid  = ['new','processing','shipped','delivered','cancelled'];

        if (!in_array($status, $valid, true)) {
            Flash::set('error', 'Неверный статус заказа');
            header('Location: /admin/orders');
            exit;
        }

        // Подгружаем заказ, чтобы проверить способ и статус оплаты
        $order = $this->orderModel->findWithItems($id);
        if (!$order) {
            Flash::set('error', 'Заказ не найден');
            header('Location: /admin/orders');
            exit;
        }

        $paymentMethod = $order['payment_method'] ?? 'cod';
        $paymentStatus = $order['payment_status'] ?? 'pending';

        // Бизнес-правило:
        // Нельзя перевести заказ со способом оплаты "СБП" в "доставлен",
        // пока платеж не в статусе "paid"
        if (
            $status === 'delivered'
            && $paymentMethod === 'sbp'
            && $paymentStatus !== 'paid'
        ) {
            Flash::set(
                'error',
                'Нельзя перевести заказ с оплатой по СБП в статус "Доставлен", пока оплата не получена.'
            );
            header('Location: /admin/orders');
            exit;
        }

        // Для заказов с оплатой при получении (и любых не-SBP):
        // если статус меняем на "доставлен" — считаем, что заказ оплачен.
        if (
            $status === 'delivered'
            && $paymentMethod !== 'sbp'
            && $paymentStatus !== 'paid'
        ) {
            // Это выставит payment_status = 'paid' и paid_at = NOW()
            $this->orderModel->markAsPaid($id);
            $paymentStatus = 'paid';
        }

        // Дополнительная аккуратность:
        // если COD-заказ отменяется до оплаты, помечаем оплату как "cancelled"
        if (
            $status === 'cancelled'
            && $paymentMethod !== 'sbp'
            && $paymentStatus === 'pending'
        ) {
            $this->orderModel->updatePayment($id, [
                'payment_status' => 'cancelled',
            ]);
            $paymentStatus = 'cancelled';
        }

        // Непосредственно обновляем статус заказа
        $this->orderModel->updateStatus($id, $status);

        Flash::set('success', 'Статус заказа обновлён');
        header('Location: /admin/orders');
        exit;
    }
}
