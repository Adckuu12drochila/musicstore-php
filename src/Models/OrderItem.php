<?php
namespace App\Models;

use PDO;

class OrderItem
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Сохраняет позиции заказа
     *
     * @param int   $orderId
     * @param array $items  каждый элемент: ['product_id','quantity','unit_price']
     */
    public function createBatch(int $orderId, array $items): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price)
             VALUES (:oid, :pid, :qty, :price)'
        );
        foreach ($items as $it) {
            $stmt->execute([
                'oid'   => $orderId,
                'pid'   => $it['product_id'],
                'qty'   => $it['quantity'],
                'price' => $it['unit_price'],
            ]);
        }
    }

    /**
     * Возвращает позиции для заказа
     */
    public function findByOrder(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT oi.*, p.name
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = :oid'
        );
        $stmt->execute(['oid' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
