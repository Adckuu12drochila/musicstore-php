<?php
// src/Controllers/PaymentController.php

namespace App\Controllers;

use PDO;
use App\Models\Order;
use App\Helpers\Flash;
use App\Services\Payment\SbpMockGateway;

class PaymentController
{
    private Order          $orderModel;
    private SbpMockGateway $gateway;

    public function __construct(PDO $pdo)
    {
        $this->orderModel = new Order($pdo);
        $this->gateway    = new SbpMockGateway($pdo);
    }

    /**
     * Страница оплаты заказа по СБП (демо).
     * GET /payment/sbp/{id}
     */
    public function showSbp(int $orderId): void
    {
        if (!isset($_SESSION['user_id'])) {
            Flash::set('error', 'Для просмотра оплаты необходимо войти в аккаунт.');
            header('Location: /login');
            exit;
        }

        $order = $this->orderModel->findWithItems($orderId);
        if (!$order) {
            http_response_code(404);
            echo 'Заказ не найден';
            return;
        }

        // Проверяем, что заказ либо принадлежит пользователю, либо он админ
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $isAdmin       = !empty($_SESSION['is_admin']);

        if (!empty($order['user_id'])
            && (int)$order['user_id'] !== $currentUserId
            && !$isAdmin
        ) {
            http_response_code(403);
            echo 'Доступ запрещён';
            return;
        }

        // Проверяем, что это заказ с оплатой по СБП
        $paymentMethod = $order['payment_method'] ?? 'cod';
        if ($paymentMethod !== 'sbp') {
            Flash::set('error', 'Для этого заказа не используется оплата по СБП.');
            header('Location: /orders/' . (int)$order['id']);
            exit;
        }

        // Если у заказа ещё нет внешнего ID платежа — создаём платёж в песочнице
        if (empty($order['payment_external_id'])) {
            $amount = (float)($order['total_amount'] ?? 0);

            $paymentData = $this->gateway->createPayment((int)$order['id'], $amount);

            // Сохраняем внешний ID платежа в заказ
            $this->orderModel->updatePayment((int)$order['id'], [
                'payment_external_id' => $paymentData['external_id'],
            ]);

            $order['payment_external_id'] = $paymentData['external_id'];
        }

        // Собираем данные для шаблона
        $payment = [
            'external_id' => $order['payment_external_id'],
            'status'      => $this->gateway->getStatusByOrderId((int)$order['id']) ?? 'pending',
            'amount'      => (float)($order['total_amount'] ?? 0),
        ];

        require __DIR__ . '/../Views/payment/sbp.php';
    }

    public function restartSbp(int $orderId): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $order = $this->orderModel->findWithItems($orderId);
        if (!$order) {
            http_response_code(404);
            echo 'Заказ не найден';
            return;
        }

        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $isAdmin       = !empty($_SESSION['is_admin']);

        // Заказ должен принадлежать пользователю или быть доступным админу
        if (!empty($order['user_id'])
            && (int)$order['user_id'] !== $currentUserId
            && !$isAdmin
        ) {
            http_response_code(403);
            echo 'Нет доступа к этому заказу';
            return;
        }

        if (($order['payment_method'] ?? 'cod') !== 'sbp') {
            Flash::set('error', 'Этот заказ не оплачивается по СБП.');
            header('Location: /orders/' . $orderId);
            exit;
        }

        if (($order['payment_status'] ?? 'pending') === 'paid') {
            Flash::set('error', 'Заказ уже оплачен, перегенерировать QR нельзя.');
            header('Location: /orders/' . $orderId);
            exit;
        }

        // Перезапускаем платёж (TTL по сути обнуляется)
        $this->gateway->restartPaymentForOrder((int)$order['id']);

        Flash::set('success', 'Сгенерирован новый QR для оплаты по СБП.');
        header('Location: /payment/sbp/' . $orderId);
        exit;
    }

    /**
     * POST /payment/sbp/{id}/simulate-success
     * Симулирует успешную оплату по СБП.
     */
    public function simulateSbpSuccess(int $orderId): void
    {
        if (!isset($_SESSION['user_id'])) {
            Flash::set('error', 'Необходимо войти в аккаунт.');
            header('Location: /login');
            exit;
        }

        $order = $this->orderModel->findWithItems($orderId);
        if (!$order) {
            http_response_code(404);
            echo 'Заказ не найден';
            return;
        }

        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $isAdmin       = !empty($_SESSION['is_admin']);

        if (!empty($order['user_id'])
            && (int)$order['user_id'] !== $currentUserId
            && !$isAdmin
        ) {
            http_response_code(403);
            echo 'Доступ запрещён';
            return;
        }

        $paymentMethod = $order['payment_method'] ?? 'cod';
        if ($paymentMethod !== 'sbp') {
            Flash::set('error', 'Для этого заказа не используется оплата по СБП.');
            header('Location: /orders/' . (int)$order['id']);
            exit;
        }

        // 1) помечаем платёж как оплаченный в таблице sbp_payments
        $this->gateway->markAsPaidByOrder((int)$order['id']);

        // 2) помечаем заказ как оплаченный (payment_status + paid_at)
        $this->orderModel->markAsPaid((int)$order['id']);

        Flash::set('success', 'Оплата по СБП успешно симулирована.');
        header('Location: /orders/' . (int)$order['id']);
        exit;
    }

    /**
     * GET /payment/sbp/{id}/status-json
     * Возвращает текущий статус платежа в JSON (для AJAX-поллинга).
     */
    public function statusJson(int $orderId): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        $order = $this->orderModel->findWithItems($orderId);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'order_not_found']);
            return;
        }

        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $isAdmin       = !empty($_SESSION['is_admin']);

        if (!empty($order['user_id'])
            && (int)$order['user_id'] !== $currentUserId
            && !$isAdmin
        ) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }

        if (($order['payment_method'] ?? 'cod') !== 'sbp') {
            http_response_code(400);
            echo json_encode(['error' => 'not_sbp_order']);
            return;
        }

        // Статус в таблице sbp_payments (мок-«банк»)
        $gatewayStatus       = $this->gateway->getStatusByOrderId((int)$order['id']) ?? 'pending';
        // Статус оплаты в самом заказе
        $orderPaymentStatus  = $order['payment_status'] ?? 'pending';

        echo json_encode([
            'status'               => $gatewayStatus,
            'order_payment_status' => $orderPaymentStatus,
            'paid_at'              => $order['paid_at'] ?? null,
        ]);
    }

}
