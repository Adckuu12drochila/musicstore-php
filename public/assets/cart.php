<?php
session_start();
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="my-4">Ваша корзина</h1>
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Корзина пуста</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Цена</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <tr>
                            <td><?= $item['name'] ?></td>
                            <td><?= $item['price'] ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form action="submit_order.php" method="POST">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Ваше имя" required>
                </div>
                <div class="mb-3">
                    <input type="tel" name="phone" class="form-control" placeholder="Телефон" required>
                </div>
                <button type="submit" class="btn btn-success">Оформить заказ</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>