# MusicStore

Небольшое MVC-приложение на PHP для интернет-магазина музыкальных инструментов.

## Содержание

* [Описание](#описание)
* [Требования](#требования)
* [Установка](#установка)
* [Настройка](#настройка)
* [Настройка БД](#настройка-бд)
* [Запуск приложения](#запуск-приложения)
* [Тестирование почты](#тестирование-почты)
* [Структура проекта](#структура-проекта)
* [Используемые библиотеки](#используемые-библиотеки)
* [Автор](#автор)

## Описание

Приложение представляет собой простой интернет-магазин музыкальных инструментов (гитары, флейты, скрипки). Реализованы:

* Регистрация и аутентификация пользователей
* Профиль пользователя с возможностью смены пароля
* Административная панель для управления товарами, категориями и заказами
* Корзина и оформление заказа
* Отправка подтверждения заказа по электронной почте через PHPMailer

## Требования

* PHP 8.0 и выше
* Расширение PDO и драйвер для PostgreSQL (`pdo_pgsql`)
* PostgreSQL 13 и выше
* Composer

## Установка

1. Клонировать репозиторий и перейти в папку проекта:

   ```bash
   git clone <url> MusicStore
   cd MusicStore
   ```
2. Установить зависимости через Composer:

   ```bash
   composer install
   composer dump-autoload
   ```

## Настройка

* Файл почтовых настроек: `config.php` (SMTP-сервер, логин, пароль, порт, шифрование и адреса):

  ```php
  return [
      'mail' => [
          'host'       => 'smtp.example.com',
          'username'   => 'user@example.com',
          'password'   => 'secret',
          'port'       => 465,
          'encryption' => 'ssl',
          'from_email' => 'no-reply@example.com',
          'from_name'  => 'MusicStore',
          'admin_email'=> 'admin@example.com',
      ],
  ];
  ```

* Файл настроек БД: `config/config.php` (подключение к PostgreSQL):

  ```php
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
  ```

## Настройка БД

1. Создать базу данных и пользователя в PostgreSQL:

   ```sql
   CREATE USER store_user WITH PASSWORD '6765';
   CREATE DATABASE music_store OWNER store_user;
   ```
2. Выполнить миграции для создания таблиц:

   ```bash
   psql -U store_user -d music_store -f db/migrations.sql
   ```
3. Загрузить тестовые данные:

   ```bash
   psql -U store_user -d music_store -f data.sql
   ```

## Запуск приложения

* Через встроенный сервер PHP (для разработки):

  ```bash
  php -S localhost:8000 -t public
  ```
* Открыть в браузере: `http://localhost:8000`

## Тестирование почты

Для проверки отправки писем:

```bash
php test_smtp.php
```

## Структура проекта

```
├─ config/            # Настройки БД
├─ db/                # SQL-миграции
├─ public/            # Фронт-контроллер и публичные файлы
├─ src/
│  ├─ Controllers/    # Контроллеры MVC
│  ├─ Models/         # Модели данных
│  ├─ Views/          # Шаблоны представлений
│  └─ Services/       # Сервисы (Mailer)
├─ vendor/            # Зависимости Composer
├─ data.sql           # Скрипт заполнения данными
├─ categories_raw.csv # Исходные CSV для категорий
├─ products_raw.csv   # Исходные CSV для товаров
└─ test_smtp.php      # Скрипт проверки SMTP
```

## Используемые библиотеки

* [PHPMailer](https://github.com/PHPMailer/PHPMailer) — отправка писем через SMTP

## Автор

Александр Смолов — разработчик данного приложения.
