<?php
// src/Controllers/CouponController.php

namespace App\Controllers;

use PDO;
use App\Models\Coupon;
use App\Helpers\Flash;

class CouponController
{
    private Coupon $couponModel;

    public function __construct(PDO $pdo)
    {
        $this->couponModel = new Coupon($pdo);
    }

    /**
     * Админ: список купонов + форма создания.
     */
    public function adminIndex(): void
    {
        $coupons = $this->couponModel->all();
        require __DIR__ . '/../Views/coupons/admin.php';
    }

    /**
     * Обработка формы создания купона.
     */
    public function store(): void
    {
        $code      = trim($_POST['code'] ?? '');
        $percent   = trim($_POST['discount_percent'] ?? '');
        $expiresAt = trim($_POST['expires_at'] ?? ''); // формат date: Y-m-d

        $errors = [];

        if ($code === '') {
            $errors[] = 'Укажите код купона.';
        }

        if ($percent === '' || !is_numeric($percent)) {
            $errors[] = 'Укажите корректный процент скидки.';
        } else {
            $p = (float)$percent;
            if ($p <= 0 || $p > 100) {
                $errors[] = 'Процент скидки должен быть от 0 до 100.';
            }
        }

        if ($expiresAt !== '') {
            $dt = \DateTime::createFromFormat('Y-m-d', $expiresAt);
            if (!$dt || $dt->format('Y-m-d') !== $expiresAt) {
                $errors[] = 'Некорректная дата истечения.';
            }
        } else {
            $expiresAt = null;
        }

        if ($code !== '' && $this->couponModel->existsByCode($code)) {
            $errors[] = 'Купон с таким кодом уже существует.';
        }

        if ($errors) {
            foreach ($errors as $e) {
                Flash::set('error', $e);
            }
            // Показываем страницу снова с текущим списком
            $coupons = $this->couponModel->all();
            require __DIR__ . '/../Views/coupons/admin.php';
            return;
        }

        $ok = $this->couponModel->create($code, (float)$percent, $expiresAt);

        if ($ok) {
            Flash::set('success', 'Купон успешно создан.');
        } else {
            Flash::set('error', 'Не удалось создать купон.');
        }

        header('Location: /admin/coupons');
        exit;
    }

    /**
     * Включить/выключить купон.
     */
    public function toggleActive(int $id): void
    {
        $this->couponModel->toggleActive($id);
        Flash::set('success', 'Статус купона обновлён.');
        header('Location: /admin/coupons');
        exit;
    }
}
