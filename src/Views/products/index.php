<?php
// src/Views/products/index.php

// 1. Заголовок страницы
$title = 'Список товаров';
require __DIR__ . '/../layout/header.php';

// Для удобства
$search     = $search     ?? '';
$categoryId = $categoryId ?? '';
$sort       = $sort       ?? '';
$products   = $products   ?? [];
$totalPages = $totalPages ?? 1;
$page       = $page       ?? 1;
?>

<!-- 2. Карусель баннеров -->
<div id="promoCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
  <div class="carousel-inner">
    <!-- Первый слайд -->
    <div class="carousel-item active">
      <img src="/assets/images/banner1.jpg" alt="Скидка" class="d-block w-100">
      <div class="carousel-caption d-none d-md-block">
        <div class="bg-dark bg-opacity-50 p-3 rounded">
          <h5>Скидка 20% на гитары</h5>
          <p>Успейте обновить инструмент до конца месяца.</p>
        </div>
      </div>
    </div>
    <!-- Второй слайд -->
    <div class="carousel-item">
      <img src="/assets/images/banner2.jpg" alt="Акция" class="d-block w-100">
      <div class="carousel-caption d-none d-md-block">
        <div class="bg-dark bg-opacity-50 p-3 rounded">
          <h5>Классика, которая не стареет</h5>
          <p>В этом месяце — доставка избранных моделей бесплатно.</p>
        </div>
      </div>
    </div>
    <!-- дополнительные слайды можно добавить сюда -->
  </div>

  <!-- Навигация карусели -->
  <button class="carousel-control-prev" type="button"
          data-bs-target="#promoCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Предыдущий</span>
  </button>
  <button class="carousel-control-next" type="button"
          data-bs-target="#promoCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Следующий</span>
  </button>
</div><!-- /#promoCarousel -->

<div class="d-flex flex-wrap align-items-baseline justify-content-between mb-3 gap-2">
  <div>
    <h1 class="h3 mb-1">Каталог товаров</h1>
    <div class="text-muted small">
      Подыщите инструмент по вкусу — воспользуйтесь поиском и фильтрами.
    </div>
  </div>
  <?php $countOnPage = count($products); ?>
  <div class="text-muted small">
    Показано: <strong><?= $countOnPage ?></strong>
    товар<?= ($countOnPage === 1 ? '' : ($countOnPage >= 2 && $countOnPage <= 4 ? 'а' : 'ов')) ?>
    на этой странице.
  </div>
</div>

<!-- 3. Блок фильтров: поиск, категория, сортировка -->
<div class="filters mb-4">
  <form method="get" class="row g-2 align-items-center mb-0">
    <div class="col-md-4">
      <label class="form-label mb-1 small text-muted">Поиск по названию</label>
      <input
        type="text"
        name="q"
        class="form-control"
        placeholder="Например, «Fender», «скрипка»..."
        value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
      >
    </div>

    <div class="col-md-3">
      <label class="form-label mb-1 small text-muted">Категория</label>
      <select name="category_id" class="form-select">
        <option value="">Все категории</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"
            <?= $categoryId === $cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label mb-1 small text-muted">Сортировка</label>
      <select name="sort" class="form-select">
        <option value="">Без сортировки</option>
        <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected' : '' ?>>Цена ↑</option>
        <option value="price_desc" <?= $sort==='price_desc' ? 'selected' : '' ?>>Цена ↓</option>
        <option value="newest"     <?= $sort==='newest'     ? 'selected' : '' ?>>Сначала новые</option>
        <option value="oldest"     <?= $sort==='oldest'     ? 'selected' : '' ?>>Сначала старые</option>
      </select>
    </div>

    <div class="col-md-2 d-flex align-items-end gap-2">
      <button class="btn btn-primary w-100">Применить</button>
      <?php if ($search || $categoryId || $sort): ?>
        <a href="/products" class="btn btn-outline-secondary" title="Сбросить фильтры">
          <i class="bi bi-x-lg"></i>
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- 4. Сетка товаров -->
<?php if (empty($products)): ?>
  <div class="profile-card p-4 text-center">
    <h5 class="mb-2">По вашему запросу товары не найдены</h5>
    <p class="text-muted mb-3">
      Попробуйте изменить параметры поиска или сбросить фильтры.
    </p>
    <a href="/products" class="btn btn-outline-primary btn-sm">
      Сбросить фильтры
    </a>
  </div>
<?php else: ?>
  <div class="row">
    <?php foreach ($products as $p): ?>
      <?php
        $stock = $p['stock'] ?? null;
        $inStock = $stock === null ? null : (int)$stock;
        $outOfStock = ($inStock !== null && $inStock <= 0);
      ?>
      <div class="col-md-4 mb-4">
        <div class="card card-product h-100 d-flex flex-column">
          <?php if (!empty($p['image_url'])): ?>
            <img
              src="<?= htmlspecialchars($p['image_url'], ENT_QUOTES, 'UTF-8') ?>"
              class="card-img-top"
              alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"
            >
          <?php endif; ?>

          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-1">
              <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
            </h5>

            <?php if ($inStock !== null): ?>
              <p class="mb-2 small">
                <?php if ($outOfStock): ?>
                  <span class="badge bg-danger-subtle text-danger">
                    Нет в наличии
                  </span>
                <?php else: ?>
                  <span class="badge bg-success-subtle text-success">
                    В наличии: <?= $inStock ?> шт.
                  </span>
                <?php endif; ?>
              </p>
            <?php endif; ?>

            <?php if (!empty($p['description'])): ?>
              <p class="card-text small text-muted mb-3">
                <?= nl2br(htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8')) ?>
              </p>
            <?php else: ?>
              <p class="card-text small text-muted mb-3">
                Описание товара будет добавлено позже.
              </p>
            <?php endif; ?>

            <div class="mt-auto">
              <p class="fw-semibold fs-5 mb-2">
                <?= number_format($p['price'], 2, ',', ' ') ?> ₽
              </p>

              <div class="d-flex justify-content-between align-items-center">
                <form action="/cart/add/<?= (int)$p['id'] ?>" method="post" class="mb-0">
                  <button
                    type="submit"
                    class="btn btn-sm btn-success"
                    <?= $outOfStock ? 'disabled' : '' ?>
                  >
                    <?= $outOfStock ? 'Нет в наличии' : 'В корзину' ?>
                  </button>
                </form>

                <?php if (!empty($_SESSION['is_admin'])): ?>
                  <div class="ms-2 d-flex gap-1">
                    <a href="/products/<?= (int)$p['id'] ?>/edit"
                       class="btn btn-sm btn-outline-secondary">
                      Правка
                    </a>
                    <form action="/products/<?= (int)$p['id'] ?>/delete"
                          method="post" class="mb-0">
                      <button class="btn btn-sm btn-outline-danger">
                        Удалить
                      </button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- 5. Пагинация -->
<?php if ($totalPages > 1): ?>
  <nav aria-label="Page navigation" class="mt-3">
    <ul class="pagination">
      <?php $base = ['q'=>$search,'category_id'=>$categoryId,'sort'=>$sort]; ?>
      <li class="page-item <?= $page<=1?'disabled':'' ?>">
        <a class="page-link"
           href="<?= $page<=1 ? '#' : ('?'.http_build_query(array_merge($base,['page'=>$page-1]))) ?>">
          Назад
        </a>
      </li>
      <?php for ($i=1; $i<=$totalPages; $i++): ?>
        <li class="page-item <?= $i===$page?'active':'' ?>">
          <a class="page-link"
             href="?<?= http_build_query(array_merge($base,['page'=>$i])) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>
      <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
        <a class="page-link"
           href="<?= $page>=$totalPages ? '#' : ('?'.http_build_query(array_merge($base,['page'=>$page+1]))) ?>">
          Вперёд
        </a>
      </li>
    </ul>
  </nav>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
