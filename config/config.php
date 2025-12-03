<?php
// config/config.php

return [
    'db' => [
        'dsn'      => 'pgsql:host=127.0.0.1;port=5432;dbname=music_store',
        'user'     => 'store_user',
        'password' => '6765',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
];