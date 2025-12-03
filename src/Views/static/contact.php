<?php
// src/Views/static/contact.php
$title = 'Контакты';
require __DIR__ . '/../layout/header.php';
?>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="profile-card p-4 h-100">
      <h1 class="h3 mb-3">Контакты</h1>
      <p class="mb-3">
        Если у вас есть вопросы по работе магазина, предложения по улучшению
        или вы нашли ошибку в проекте — напишите, я постараюсь ответить.
      </p>

      <ul class="list-unstyled mb-3">
        <li class="mb-2">
          <i class="bi bi-telephone me-1"></i>
          <strong>Телефон:</strong> +7 (495) 123-45-67
        </li>
        <li class="mb-2">
          <i class="bi bi-envelope me-1"></i>
          <strong>Email:</strong>
          <a href="mailto:alexander.mailing.list.box.1@mail.ru">
            alexander.mailing.list.box.1@mail.ru
          </a>
        </li>
        <li class="mb-2">
          <i class="bi bi-geo-alt me-1"></i>
          <strong>Адрес:</strong> г. Москва, Зеленоград, к1522
        </li>
      </ul>

      <p class="mb-1">
        <i class="bi bi-clock me-1"></i>
        На связи по будням с <strong>12:00 до 22:00</strong>.
      </p>
      <p class="mb-0 text-muted small">
        Обычно отвечаю в течение <strong>1 рабочего дня</strong>.
      </p>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="profile-card p-4 h-100">
      <h4 class="mb-3">
        <i class="bi bi-info-circle me-1"></i>
        Как лучше сформулировать запрос
      </h4>
      <ul class="list-unstyled mb-3">
        <li class="mb-2">
          <i class="bi bi-dot me-1"></i>
          Если проблема связана с заказом — укажите номер заказа
          и приблизительное время оформления.
        </li>
        <li class="mb-2">
          <i class="bi bi-dot me-1"></i>
          Если нашли баг — опишите шаги воспроизведения и страницу,
          на которой он проявился.
        </li>
        <li class="mb-2">
          <i class="bi bi-dot me-1"></i>
          Если есть идеи по доработке — кратко опишите сценарий,
          который хотелось бы добавить.
        </li>
      </ul>

      <p class="text-muted small mb-0">
        Проект носит учебный характер, поэтому любые отзывы по коду,
        архитектуре и UX тоже приветствуются.
      </p>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
