<?php
// src/Views/products/form.php

$title = !empty($product['id']) ? 'Редактирование товара' : 'Новый товар';
require __DIR__ . '/../layout/header.php';

$product = $product ?? [];
?>

<h2 class="mb-4"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>

<div class="profile-card p-4">
  <form
    action="<?= !empty($product['id']) ? '/products/'.$product['id'] : '/products' ?>"
    method="post"
    enctype="multipart/form-data"
  >
    <div class="row g-4">
      <!-- Основные поля -->
      <div class="col-lg-8">
        <h5 class="mb-3">Основная информация</h5>

        <div class="mb-3">
          <label for="category_id" class="form-label">Категория</label>
          <select id="category_id" name="category_id" class="form-select" required>
            <option value="">— Выберите категорию —</option>
            <?php foreach ($categories as $catItem): ?>
              <option value="<?= $catItem['id'] ?>"
                <?= (isset($product['category_id']) && $product['category_id'] === $catItem['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($catItem['name'], ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="name" class="form-label">Название</label>
          <input
            type="text"
            id="name"
            name="name"
            class="form-control"
            required
            value="<?= htmlspecialchars($product['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          >
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Описание</label>
          <textarea
            id="description"
            name="description"
            class="form-control"
            rows="4"
          ><?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          <div class="form-text">
            Кратко опишите особенности инструмента, комплектацию, материал, производителя.
          </div>
        </div>
      </div>

      <!-- Цена / остаток / изображение -->
      <div class="col-lg-4">
        <h5 class="mb-3">Параметры и изображение</h5>

        <div class="mb-3">
          <label for="price" class="form-label">Цена, ₽</label>
          <input
            type="number"
            id="price"
            name="price"
            step="0.01"
            min="0"
            class="form-control"
            required
            value="<?= htmlspecialchars($product['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          >
        </div>

        <div class="mb-3">
          <label for="stock" class="form-label">Остаток на складе, шт.</label>
          <input
            type="number"
            id="stock"
            name="stock"
            min="0"
            class="form-control"
            required
            value="<?= htmlspecialchars($product['stock'] ?? '0', ENT_QUOTES, 'UTF-8') ?>"
          >
          <div class="form-text">
            Можно использовать 0, если товар временно недоступен.
          </div>
        </div>

        <div class="mb-3">
          <label for="image" class="form-label">Изображение</label>
          <?php if (!empty($product['image_url'])): ?>
            <div class="mb-2">
              <img
                src="<?= htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                alt="Превью товара"
                class="img-fluid rounded border"
              >
            </div>
          <?php endif; ?>
          <input
            type="file"
            id="image"
            name="image"
            accept="image/*"
            class="form-control"
          >
          <div class="form-text">
            Загрузите изображение в форматах JPG, PNG или WEBP.
          </div>
        </div>
      </div>
    </div>

    <div class="mt-3 d-flex gap-2">
      <button type="submit" class="btn btn-success">
        Сохранить
      </button>
      <a href="/products" class="btn btn-secondary">
        Отмена
      </a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
