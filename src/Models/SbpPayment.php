<?php
// src/Models/SbpPayment.php

namespace App\Models;

use PDO;

class SbpPayment
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Все платежи по СБП для заказа.
     * Сейчас у тебя фактически один платёж на заказ,
     * но структура уже готова под несколько попыток.
     */
    public function findByOrder(int $orderId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id,
                   external_id,
                   order_id,
                   amount,
                   status,
                   created_at,
                   updated_at
            FROM sbp_payments
            WHERE order_id = :order_id
            ORDER BY created_at ASC, id ASC
        ");
        $stmt->execute(['order_id' => $orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
