<?php
// config.php

$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
    // Работаем в Railway: парсим DATABASE_URL
    $parts = parse_url($databaseUrl);
    if ($parts === false) {
        throw new RuntimeException('Invalid DATABASE_URL');
    }

    $host = $parts['host'] ?? 'localhost';
    $port = $parts['port'] ?? 5432;
    $name = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
    $user = $parts['user'] ?? 'postgres';
    $pass = $parts['pass'] ?? '';

} else {
    // Локальная разработка
    $host = '127.0.0.1';
    $port = 5432;
    $name = 'music_store';
    $user = 'postgres';
    $pass = 'postgres'; // твой локальный пароль

}

// Собираем DSN
$dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $name);

// На всякий случай логируем, какой DSN реально используется
error_log('DB DSN USED: ' . $dsn);

return [
    'db' => [
        'dsn'      => $dsn,
        'user'     => $user,
        'password' => $pass,
    ],

    // остальной конфиг почты и т.п.
    'mail' => [
        'host'        => getenv('MAIL_HOST')        ?: 'smtp.mail.ru',
        'username'    => getenv('MAIL_USER')        ?: 'alexander.mailing.list.box.1@mail.ru',
        'password'    => getenv('MAIL_PASSWORD')    ?: 'UcfVQLEdqPOqZDPQDnpj',
        'port'        => (int)(getenv('MAIL_PORT') ?: 465),
        'encryption'  => getenv('MAIL_ENCRYPTION')  ?: 'ssl',
        'from_email'  => getenv('MAIL_FROM_EMAIL')  ?: 'alexander.mailing.list.box.1@mail.ru',
        'from_name'   => getenv('MAIL_FROM_NAME')   ?: 'MusicStore',
        'admin_email' => getenv('ADMIN_EMAIL')      ?: 'alexander.mailing.list.box.1@mail.ru',
    ],
];
