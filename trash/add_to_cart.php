<?php
// Подключаем PDO-соединение
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../db_connect.php';

session_start();
include 'db_connect.php';

$product_id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][] = $product;
header("Location: index.php");
?>