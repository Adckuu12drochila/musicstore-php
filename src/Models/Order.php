<?php
namespace App\Models;

use PDO;

class Order
{
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    /**
     * Создаёт новый заказ и возвращает его ID
     * @param array $data ['user_id','client_name','phone','delivery_address']
     */
    /**
     * Создаёт новый заказ и возвращает его ID
     *
     * @param array $data [
     *   'user_id',
     *   'client_name',
     *   'phone',
     *   'delivery_address',
     *   // опционально:
     *   'payment_method',
     *   'payment_status',
     *   'payment_external_id',
     *   'total_amount',
     *   'paid_at',
     * ]
     */
    public function create(array $data): int
    {
        // Значения по умолчанию, чтобы не ломать старый код
        $paymentMethod     = $data['payment_method']      ?? 'cod';
        $paymentStatus     = $data['payment_status']      ?? 'pending';
        $paymentExternalId = $data['payment_external_id'] ?? null;
        $totalAmount       = $data['total_amount']        ?? 0;
        $paidAt            = $data['paid_at']             ?? null;

        $stmt = $this->db->prepare(
            'INSERT INTO orders
               (user_id, client_name, phone, delivery_address, status,
                payment_method, payment_status, payment_external_id, total_amount, paid_at)
             VALUES
               (:user_id, :client_name, :phone, :delivery_address, :status,
                :payment_method, :payment_status, :payment_external_id, :total_amount, :paid_at)'
        );

        $stmt->execute([
            'user_id'             => $data['user_id'],
            'client_name'         => $data['client_name'],
            'phone'               => $data['phone'],
            'delivery_address'    => $data['delivery_address'],
            'status'              => 'new',
            'payment_method'      => $paymentMethod,
            'payment_status'      => $paymentStatus,
            'payment_external_id' => $paymentExternalId,
            'total_amount'        => $totalAmount,
            'paid_at'             => $paidAt,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Возвращает все заказы пользователя
     * @param int $userId
     * @return array
     */
    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC'
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Возвращает один заказ по ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $o = $stmt->fetch(PDO::FETCH_ASSOC);
        return $o ?: null;
    }
    /** Возвращает все заказы в системе (для админа) */
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM orders ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Меняет статус заказа */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE orders SET status = :status WHERE id = :id'
        );
        return $stmt->execute([
            'id'     => $id,
            'status' => $status,
        ]);
    }

    /**
     * Обновляет платёжные поля заказа.
     *
     * Можно передавать любой подмножество:
     * [
     *   'payment_method'      => 'sbp' | 'cod' | ...,
     *   'payment_status'      => 'pending' | 'paid' | 'failed' | 'cancelled',
     *   'payment_external_id' => 'SBP-XXXX',
     *   'total_amount'        => 1234.56,
     *   'paid_at'             => '2025-12-02 15:30:00',
     * ]
     */
    public function updatePayment(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (array_key_exists('payment_method', $data)) {
            $fields[] = 'payment_method = :payment_method';
            $params['payment_method'] = $data['payment_method'];
        }
        if (array_key_exists('payment_status', $data)) {
            $fields[] = 'payment_status = :payment_status';
            $params['payment_status'] = $data['payment_status'];
        }
        if (array_key_exists('payment_external_id', $data)) {
            $fields[] = 'payment_external_id = :payment_external_id';
            $params['payment_external_id'] = $data['payment_external_id'];
        }
        if (array_key_exists('total_amount', $data)) {
            $fields[] = 'total_amount = :total_amount';
            $params['total_amount'] = $data['total_amount'];
        }
        if (array_key_exists('paid_at', $data)) {
            $fields[] = 'paid_at = :paid_at';
            $params['paid_at'] = $data['paid_at'];
        }

        if (!$fields) {
            // Нечего обновлять
            return false;
        }

        $sql = 'UPDATE orders SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Помечает заказ как оплаченный (для платёжного шлюза / песочницы).
     */
    public function markAsPaid(int $id): bool
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        return $this->updatePayment($id, [
            'payment_status' => 'paid',
            'paid_at'        => $now,
        ]);
    }

    public function findWithItems(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, u.email AS user_email
             FROM orders o
             LEFT JOIN users u ON u.id = o.user_id
             WHERE o.id = :id'
        );
        $stmt->execute(['id'=>$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) return null;

        $stmt2 = $this->db->prepare(
            'SELECT oi.*, p.name
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = :id'
        );
        $stmt2->execute(['id'=>$id]);
        $order['items'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $order;
    }
}
