<?php
// config.php — конфиг под Railway

// Для отладки: видно в логах, что конфиг вообще загрузился
error_log('CONFIG LOADED (Railway)');

$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) {
    // Никаких локальных дефолтов — если переменная не задана, сразу падаем
    throw new RuntimeException('DATABASE_URL is not set in environment');
}

$parts = parse_url($databaseUrl);
if ($parts === false || !isset($parts['host'], $parts['path'])) {
    throw new RuntimeException('Invalid DATABASE_URL: ' . $databaseUrl);
}

$host = $parts['host'];
$port = $parts['port'] ?? 5432;
$name = ltrim($parts['path'], '/');
$user = $parts['user'] ?? 'postgres';
$pass = $parts['pass'] ?? '';

// Собираем DSN для PDO
$dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $name);

// Логируем только хост и БД (без пароля)
error_log('DB DSN USED: ' . $dsn);

return [
    'db' => [
        'dsn'      => $dsn,
        'user'     => $user,
        'password' => $pass,
    ],

    // Почта — как у тебя была (можно тоже вынести в env по желанию)
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
