<?php
// config.php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '5432';
$name = getenv('DB_NAME') ?: 'music_store';

// Если явно не передали DB_DSN, собираем DSN из host/port/name
$dsn = getenv('DB_DSN') ?: sprintf(
    'pgsql:host=%s;port=%s;dbname=%s',
    $host,
    $port,
    $name
);

return [
    'db' => [
        'dsn'      => $dsn,
        'user'     => getenv('DB_USER')     ?: 'postgres',
        'password' => getenv('DB_PASSWORD') ?: 'postgres',
    ],

    // остальной конфиг почты и т.п. как у тебя был
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