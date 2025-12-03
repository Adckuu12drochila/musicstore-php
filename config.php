<?php
// config.php — только Railway

// 1. Пытаемся взять строку подключения из переменной окружения DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');

// 2. Если переменной нет — жёстко прописываем строку от Railway
if (!$databaseUrl) {
    $databaseUrl = 'postgresql://postgres:cnnupLLTsUgpYHdfILyYNfKDyaFcZNQU@postgres.railway.internal:5432/railway';
}

// 3. Разбираем URL
$parts = parse_url($databaseUrl);
if ($parts === false) {
    throw new RuntimeException('Invalid DATABASE_URL: ' . $databaseUrl);
}

$host = $parts['host'] ?? 'postgres.railway.internal';
$port = $parts['port'] ?? 5432;
$name = isset($parts['path']) ? ltrim($parts['path'], '/') : 'railway';
$user = $parts['user'] ?? 'postgres';
$pass = $parts['pass'] ?? '';

// 4. Собираем DSN для PDO
$dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $name);

// Немного логов в stderr (видно в Railway Logs)
error_log('DB DSN USED: ' . $dsn);

return [
    'db' => [
        'dsn'      => $dsn,
        'user'     => $user,
        'password' => $pass,
    ],

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
