<?php
// src/Views/static/about.php
$title = 'О проекте';
require __DIR__ . '/../layout/header.php';
?>

<div class="profile-card p-4 mb-4">
  <h1 class="h3 mb-3">О MusicStore</h1>
  <p class="mb-3">
    <strong>MusicStore</strong> — учебный онлайн-магазин музыкальных инструментов,
    собранный как полноценное web-приложение: с каталогом, корзиной, оформлением заказа,
    почтовыми уведомлениями и демо-оплатой по СБП с QR-кодом.
  </p>

  <div class="row g-4">
    <div class="col-md-6">
      <h5 class="mb-2">
        <i class="bi bi-music-note-beamed me-1"></i>
        Что умеет магазин
      </h5>
      <ul class="list-unstyled mb-0">
        <li class="mb-1">
          <i class="bi bi-check2-circle text-success me-1"></i>
          Каталог гитар, клавишных, ударных и аксессуаров.
        </li>
        <li class="mb-1">
          <i class="bi bi-check2-circle text-success me-1"></i>
          Фильтрация и сортировка по категориям, цене и названию.
        </li>
        <li class="mb-1">
          <i class="bi bi-check2-circle text-success me-1"></i>
          Карточки товаров с фото, описанием и быстрым добавлением в корзину.
        </li>
        <li class="mb-1">
          <i class="bi bi-check2-circle text-success me-1"></i>
          Корзина с мгновенным пересчётом итоговой суммы.
        </li>
        <li class="mb-1">
          <i class="bi bi-check2-circle text-success me-1"></i>
          Оформление заказа с подтверждением по email (PHPMailer).
        </li>
        <li class="mb-1">
          <i class="bi bi-check2-circle text-success me-1"></i>
          Личный кабинет с историей заказов и таймлайном статусов.
        </li>
      </ul>
    </div>

    <div class="col-md-6">
      <h5 class="mb-2">
        <i class="bi bi-gear-wide-connected me-1"></i>
        Технологии и стек
      </h5>
      <ul class="list-unstyled mb-0">
        <li class="mb-1">
          <i class="bi bi-dot me-1"></i>
          Backend: чистый PHP 8 + PDO поверх PostgreSQL.
        </li>
        <li class="mb-1">
          <i class="bi bi-dot me-1"></i>
          Архитектура: простое MVC, контроллеры + модели + представления.
        </li>
        <li class="mb-1">
          <i class="bi bi-dot me-1"></i>
          Email-уведомления: PHPMailer (подтверждение заказа и письма админу).
        </li>
        <li class="mb-1">
          <i class="bi bi-dot me-1"></i>
          Frontend: Bootstrap 5, Bootstrap Icons, адаптивный дизайн.
        </li>
        <li class="mb-1">
          <i class="bi bi-dot me-1"></i>
          Темы: переключение светлой/тёмной темы с сохранением в localStorage.
        </li>
        <li class="mb-1">
          <i class="bi bi-dot me-1"></i>
          Демонстрация оплаты по СБП: мок-шлюз, QR-код, таймер жизни платежа, перегенерация.
        </li>
        <li class="mb-1">
          <i class="bi bi-dot me-1"></i>
          Промокоды и скидки: купоны в админке, пересчёт итоговой суммы заказа.
        </li>
      </ul>
    </div>
  </div>

  <hr class="my-4">

  <div class="row g-4">
    <div class="col-md-6">
      <h5 class="mb-2">
        <i class="bi bi-shield-check me-1"></i>
        Безопасность и данные
      </h5>
      <ul class="list-unstyled mb-0">
        <li class="mb-1">
          <i class="bi bi-lock-fill me-1"></i>
          Доступ к базе — через подготовленные запросы PDO (prepared statements).
        </li>
        <li class="mb-1">
          <i class="bi bi-lock-fill me-1"></i>
          Хранение паролей — в виде хеша (password_hash / password_verify).
        </li>
        <li class="mb-1">
          <i class="bi bi-lock-fill me-1"></i>
          Разделение прав: пользователь / администратор, защита админ-маршрутов.
        </li>
      </ul>
    </div>

    <div class="col-md-6">
      <h5 class="mb-2">
        <i class="bi bi-lightning-charge-fill me-1"></i>
        Для чего создан проект
      </h5>
      <p class="mb-2">
        MusicStore задуман как учебный проект, демонстрирующий
        «живой» интернет-магазин на чистом PHP без тяжёлых фреймворков:
        с аутентификацией, корзиной, заказами, промокодами, демо-платежами
        и админ-панелью.
      </p>
      <p class="mb-0 text-muted small">
        Проект постоянно дорабатывается: добавляются визуальные улучшения,
        новые сценарии в админке и дополнительные «фишки» вокруг оплаты по СБП.
      </p>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
