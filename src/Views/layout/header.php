<?php
// src/Views/layout/header.php

use App\Helpers\Flash;

// Забираем все flash-сообщения и очищаем их
$flashes = Flash::get();
$uri     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Музыкальный магазин', ENT_QUOTES, 'UTF-8') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet" integrity="sha384-…" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap"
        rel="stylesheet">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="/">
      <i class="bi bi-music-note-beamed me-2"></i>
      <span>MusicStore</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Левая часть навигации -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!-- Товары: доступно всем -->
        <li class="nav-item">
          <a class="nav-link <?= $uri === '/products' ? 'active' : '' ?>"
             href="/products">Товары</a>
        </li>

        <!-- Корзина: доступно всем -->
        <li class="nav-item">
          <a class="nav-link <?= $uri === '/cart' ? 'active' : '' ?>"
             href="/cart">
            Корзина
            <?php if (!empty($_SESSION['cart'])): ?>
              (<?= array_sum($_SESSION['cart']) ?>)
            <?php endif; ?>
          </a>
        </li>

        <!-- Мои заказы: только авторизованным -->
        <?php if (!empty($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="nav-link <?= $uri === '/orders' ? 'active' : '' ?>"
               href="/orders">Мои заказы</a>
          </li>
        <?php endif; ?>

        <!-- Админка: только для админов -->
        <?php if (!empty($_SESSION['is_admin'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle
               <?= (strpos($uri, '/admin') === 0 || strpos($uri, '/categories') === 0) ? 'active' : '' ?>"
               href="#" id="adminMenu" role="button" data-bs-toggle="dropdown"
               aria-expanded="false">
              Админка
            </a>
            <ul class="dropdown-menu" aria-labelledby="adminMenu">
              <li class="dropdown-header small text-muted px-3">
                Товары и категории
              </li>
              <li>
                <a class="dropdown-item <?= $uri === '/products' ? 'active' : '' ?>"
                   href="/products">Список товаров</a>
              </li>
              <li>
                <a class="dropdown-item <?= $uri === '/products/create' ? 'active' : '' ?>"
                   href="/products/create">Создать товар</a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item <?= $uri === '/categories' ? 'active' : '' ?>"
                   href="/categories">Список категорий</a>
              </li>
              <li>
                <a class="dropdown-item <?= $uri === '/categories/create' ? 'active' : '' ?>"
                   href="/categories/create">Создать категорию</a>
              </li>

              <li><hr class="dropdown-divider"></li>
              <li class="dropdown-header small text-muted px-3">
                Заказы и клиенты
              </li>
              <li>
                <a class="dropdown-item <?= strpos($uri, '/admin/orders') === 0 ? 'active' : '' ?>"
                   href="/admin/orders">Управление заказами</a>
              </li>
              <li>
                <a class="dropdown-item <?= strpos($uri, '/admin/coupons') === 0 ? 'active' : '' ?>"
                   href="/admin/coupons">Купоны / промокоды</a>
              </li>
              <li>
                <a class="dropdown-item <?= strpos($uri, '/admin/users') === 0 ? 'active' : '' ?>"
                   href="/admin/users">Управление пользователями</a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>

      <!-- Правая часть навигации -->
      <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
        <?php if (empty($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="nav-link <?= $uri === '/login' ? 'active' : '' ?>"
               href="/login">Войти</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $uri === '/register' ? 'active' : '' ?>"
               href="/register">Регистрация</a>
          </li>
        <?php else: ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center"
               href="#" id="userMenu" role="button"
               data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-1 fs-4"></i>
              <?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
              <li><a class="dropdown-item" href="/profile">Мой профиль</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="/logout">Выход</a></li>
            </ul>
          </li>
        <?php endif; ?>

        <!-- Переключатель тёмной темы (один общий) -->
        <li class="nav-item ms-2">
          <button id="theme-toggle" class="btn btn-sm btn-outline-secondary">
            🌙
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4 flex-fill">
  <!-- Flash-сообщения -->
  <?php foreach (['success','error'] as $type): ?>
    <?php if (!empty($flashes[$type])): ?>
      <?php foreach ($flashes[$type] as $msg): ?>
        <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php endforeach; ?>
