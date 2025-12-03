<?php

namespace App\Controllers;

use PDO;
use App\Models\Product;
use App\Helpers\Flash;

class CartController
{
    private Product $productModel;

    public function __construct(PDO $pdo)
    {
        $this->productModel = new Product($pdo);
        // Убедимся, что корзина инициализирована
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Добавить товар в корзину
     */
    public function add(int $productId): void
    {
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
        Flash::set('success', 'Товар добавлен в корзину');
        header('Location: /cart');
        exit;
    }

    /**
     * Удалить товар из корзины
     */
    public function remove(int $productId): void
    {
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            Flash::set('success', 'Товар удалён из корзины');
        }
        header('Location: /cart');
        exit;
    }

    /**
     * Показать содержимое корзины
     */
    public function show(): void
    {
        $cart       = $_SESSION['cart'];
        $items      = [];
        $totalPrice = 0.0;

        foreach ($cart as $productId => $quantity) {
            $product = $this->productModel->find($productId);
            if ($product) {
                $product['quantity'] = $quantity;
                $product['subtotal'] = $product['price'] * $quantity;
                $totalPrice         += $product['subtotal'];
                $items[]             = $product;
            }
        }

        // Теперь передаем в шаблон $items и $totalPrice
        require __DIR__ . '/../Views/cart/index.php';
    }
}
