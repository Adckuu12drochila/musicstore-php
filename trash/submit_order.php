<?php
// Подключаем PDO-соединение
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../db_connect.php';

session_start();
include 'db_connect.php';

if (!empty($_SESSION['cart'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $products = json_encode($_SESSION['cart']);

    $sql = "INSERT INTO orders (client_name, phone, product_ids) VALUES ('$name', '$phone', '$products')";
    if ($conn->query($sql) === TRUE) {
        unset($_SESSION['cart']);
        echo "<script>alert('Заказ оформлен! Мы свяжемся с вами.'); window.location.href='index.php';</script>";
    } else {
        echo "Ошибка: " . $sql . "<br>" . $conn->error;
    }
}
?>