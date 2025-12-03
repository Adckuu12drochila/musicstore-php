<?php
// src/Views/orders/_payment_helpers.php

if (!function_exists('paymentMethodLabel')) {
    function paymentMethodLabel(?string $method): string
    {
        $method = $method ?? 'cod';

        return match ($method) {
            'sbp' => 'СБП (демо)',
            'cod' => 'Оплата при получении',
            default => ucfirst($method),
        };
    }
}

if (!function_exists('paymentStatusBadge')) {
    /**
     * Возвращает [label, cssClass] для статуса оплаты.
     */
    function paymentStatusBadge(?string $status): array
    {
        $status = $status ?? 'pending';

        return match ($status) {
            'paid'      => ['Оплачен', 'badge bg-success'],
            'pending'   => ['Ожидает оплаты', 'badge bg-warning text-dark'],
            'failed'    => ['Ошибка оплаты', 'badge bg-danger'],
            'cancelled' => ['Отменён', 'badge bg-secondary'],
            'expired'   => ['Истёк', 'badge bg-secondary'],
            default     => [ucfirst($status), 'badge bg-light text-dark'],
        };
    }
}

if (!function_exists('paymentStatusBadgeHtml')) {
    /**
     * Готовый HTML-бейдж статуса оплаты.
     * Не ломает старые вызовы paymentStatusBadge(), которые получают [label, class].
     */
    function paymentStatusBadgeHtml(?string $paymentMethod, ?string $status): string
    {
        // Пока paymentMethod нам не нужен для выбора цвета, но параметр оставляем "на вырост".
        [$label, $class] = paymentStatusBadge($status);

        return sprintf(
            '<span class="%s">%s</span>',
            htmlspecialchars($class, ENT_QUOTES),
            htmlspecialchars($label, ENT_QUOTES)
        );
    }
}

if (!function_exists('paymentRowClass')) {
    /**
     * Старая функция: класс строки по статусу оплаты.
     * Оставляем для совместимости, но в админке будем использовать adminOrderRowClass().
     */
    function paymentRowClass(?string $status): string
    {
        $status = $status ?? 'pending';

        return match ($status) {
            'pending'   => 'table-warning',
            'failed'    => 'table-danger',
            'cancelled' => 'table-secondary',
            'paid'      => 'table-success',
            default     => '',
        };
    }
}

if (!function_exists('adminOrderRowClass')) {
    /**
     * Класс строки в админ-таблице заказов.
     * Цвет отражает только статус заказа (для всех методов оплаты одинаково).
     */
    function adminOrderRowClass(
        ?string $paymentMethod,
        ?string $paymentStatus,
        ?string $orderStatus
    ): string {
        $orderStatus = $orderStatus ?? 'new';

        return match ($orderStatus) {
            'new'        => 'table-warning',   // новый заказ
            'processing' => 'table-info',      // в обработке
            'shipped'    => 'table-primary',   // отправлен
            'delivered'  => 'table-success',   // доставлен
            'cancelled'  => 'table-secondary', // отменён
            default      => '',
        };
    }
}

