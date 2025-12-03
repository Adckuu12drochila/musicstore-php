<?php
// db_connect.php

// Подключаем конфигурацию
$config = require __DIR__ . '/config/config.php';
$db     = $config['db'];

try {
    // Создаём PDO-соединение по параметрам из конфига
    $pdo = new PDO(
        $db['dsn'],
        $db['user'],
        $db['password'],
        $db['options']
    );
    // На всякий случай явно установим клиентскую кодировку
    $pdo->exec("SET NAMES 'UTF8'");
    $pdo->exec("SET client_encoding TO 'UTF8'");
} catch (PDOException $e) {
    // Если не удалось подключиться — выводим ошибку и останавливаем скрипт
    die('Ошибка подключения к БД: ' . $e->getMessage());
}
