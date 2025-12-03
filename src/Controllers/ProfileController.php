<?php
namespace App\Controllers;

use PDO;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Helpers\Flash;

class ProfileController
{
    private User      $userModel;
    private Order     $orderModel;
    private OrderItem $orderItemModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel      = new User($pdo);
        $this->orderModel     = new Order($pdo);
        $this->orderItemModel = new OrderItem($pdo);
    }

    // GET /profile
    public function show(): void
    {
        $userId = $_SESSION['user_id'];
        $user   = $this->userModel->findById($userId);

        // Получаем сами заказы
        $orders = $this->orderModel->findByUser($userId);

        // И «пришиваем» к каждому список позиций
        foreach ($orders as &$order) {
            $order['items'] = $this->orderItemModel->findByOrder((int)$order['id']);
        }
        unset($order);

        require __DIR__ . '/../Views/profile/index.php';
    }

    // POST /profile
    public function update(): void { /* … */ }

    // POST /profile/password
    public function changePassword(): void { /* … */ }
}
