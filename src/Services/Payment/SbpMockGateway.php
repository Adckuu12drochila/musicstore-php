<?php
// src/Services/Payment/SbpMockGateway.php

namespace App\Services\Payment;

use PDO;

class SbpMockGateway
{
    // Время жизни QR-кода в секундах (5 минут)
    public const TTL_SECONDS = 300;

    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Создаёт "платёж" в песочнице для указанного заказа.
     * Возвращает внешний ID платежа и сумму.
     */
    public function createPayment(int $orderId, float $amount): array
    {
        // Псевдо-идентификатор платежа в системе СБП
        $externalId = 'SBP-' . bin2hex(random_bytes(8));

        $stmt = $this->db->prepare(
            'INSERT INTO sbp_payments (external_id, order_id, amount, status)
             VALUES (:external_id, :order_id, :amount, :status)'
        );

        $stmt->execute([
            'external_id' => $externalId,
            'order_id'    => $orderId,
            'amount'      => $amount,
            'status'      => 'pending',
        ]);

        return [
            'external_id' => $externalId,
            'amount'      => $amount,
        ];
    }

    /**
     * Возвращает статус платежа по order_id (последняя запись).
     */
    public function getStatusByOrderId(int $orderId): ?string
    {
        $stmt = $this->db->prepare("
            SELECT status, created_at
            FROM sbp_payments
            WHERE order_id = :order_id
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute(['order_id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $status = $row['status'] ?? 'pending';

        // Если платёж всё ещё pending, смотрим TTL от created_at
        if ($status === 'pending' && !empty($row['created_at'])) {
            $createdTs = strtotime($row['created_at']);
            $expiresTs = $createdTs + self::TTL_SECONDS;

            if (time() >= $expiresTs) {
                // Логически считаем платёж истёкшим
                return 'expired';
            }
        }

        return $status;
    }

    public function restartPaymentForOrder(int $orderId): void
    {
        // Сбрасываем платёж в состояние "pending" и обновляем created_at/updated_at,
        // как будто сгенерировали новый QR для того же платежа.
        $stmt = $this->db->prepare("
            UPDATE sbp_payments
            SET status     = 'pending',
                created_at = NOW(),
                updated_at = NOW()
            WHERE order_id = :order_id
        ");
        $stmt->execute(['order_id' => $orderId]);
    }


    /**
     * Помечает платеж как оплаченный (для песочницы).
     */
    public function markAsPaidByOrder(int $orderId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE sbp_payments
                SET status = :status,
                    updated_at = NOW()
              WHERE order_id = :order_id'
        );

        return $stmt->execute([
            'status'   => 'paid',
            'order_id' => $orderId,
        ]);
    }
}
