<?php
// src/Views/categories/form.php

// Заголовок страницы
$title = !empty($category['id'])
    ? 'Редактирование категории'
    : 'Новая категория';

require __DIR__ . '/../layout/header.php';
?>

<h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>

<form
    action="<?= !empty($category['id'])
        ? '/categories/' . $category['id']
        : '/categories' ?>"
    method="post"
>
    <div class="mb-3">
        <label for="name" class="form-label">Название</label>
        <input
            type="text"
            id="name"
            name="name"
            class="form-control"
            required
            value="<?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        >
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Описание</label>
        <textarea
            id="description"
            name="description"
            class="form-control"
            rows="4"
        ><?= htmlspecialchars($category['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <button type="submit" class="btn btn-success">
        <?= !empty($category['id']) ? 'Обновить' : 'Создать' ?>
    </button>
    <a href="/categories" class="btn btn-secondary">Отмена</a>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
