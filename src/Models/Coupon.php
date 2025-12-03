<?php
// src/Models/Coupon.php

namespace App\Models;

use PDO;

class Coupon
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Для применения промокода при заказе.
     */
    public function findActiveByCode(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT id, code, discount_percent, is_active, expires_at
            FROM coupons
            WHERE LOWER(code) = LOWER(:code)
              AND is_active = TRUE
              AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1
        ");
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Все купоны для админки.
     */
    public function all(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, code, discount_percent, is_active, expires_at
            FROM coupons
            ORDER BY id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Проверка, существует ли купон с таким кодом (без учета регистра).
     */
    public function existsByCode(string $code): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM coupons
            WHERE LOWER(code) = LOWER(:code)
            LIMIT 1
        ");
        $stmt->execute(['code' => $code]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Создание купона.
     */
    public function create(string $code, float $discountPercent, ?string $expiresDate): bool
    {
        $params = [
            'code'             => $code,
            'discount_percent' => $discountPercent,
        ];

        if ($expiresDate !== null) {
            // ожидаем формат Y-m-d, приводим к TIMESTAMP
            $sql = "
                INSERT INTO coupons (code, discount_percent, expires_at)
                VALUES (:code, :discount_percent, :expires_at)
            ";
            $params['expires_at'] = $expiresDate . ' 23:59:59';
        } else {
            $sql = "
                INSERT INTO coupons (code, discount_percent)
                VALUES (:code, :discount_percent)
            ";
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Переключить is_active (вкл/выкл).
     */
    public function toggleActive(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE coupons
            SET is_active = NOT is_active
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $id]);
    }
}
