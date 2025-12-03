<?php
// config.php

$railway = getenv('RAILWAY_ENVIRONMENT') || getenv('RAILWAY_PROJECT_ID');

if ($railway) {
    // Работаем в Railway
    $dsn  = 'pgsql:host=postgres.railway.internal;port=5432;dbname=railway';
    $user = 'postgres';
    $pass = 'ТВОЙ_ПАРОЛЬ_ОТ_RAILWAY';
} else {
    // Локальная разработка
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '5432';
    $name = getenv('DB_NAME') ?: 'music_store';

    $dsn = getenv('DB_DSN') ?: sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $host,
        $port,
        $name
    );

    $user = getenv('DB_USER')     ?: 'postgres';
    $pass = getenv('DB_PASSWORD') ?: 'postgres';
}

return [
    'db' => [
        'dsn'      => $dsn,
        'user'     => getenv('DB_USER')     ?: 'postgres',
        'password' => getenv('DB_PASSWORD') ?: 'postgres',
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