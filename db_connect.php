<?php
// db_connect.php — единая точка подключения к БД
declare(strict_types=1);

use PDO;
use PDOException;

// Подтягиваем конфиг (тот самый, что в корне)
$config = require __DIR__ . '/config.php';

$db = $config['db'] ?? null;
if (!$db || empty($db['dsn']) || empty($db['user'])) {
    throw new RuntimeException('DB config is not set correctly in config.php');
}

try {
    $pdo = new PDO(
        $db['dsn'],
        $db['user'],
        $db['password'] ?? '',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // В Railway это сообщение увидишь в логах
    error_log('DB CONNECTION ERROR: ' . $e->getMessage());
    http_response_code(500);
    echo 'Ошибка подключения к БД: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}
