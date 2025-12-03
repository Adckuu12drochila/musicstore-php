<?php
// src/Views/categories/index.php

$title = 'Категории';
require __DIR__ . '/../layout/header.php';
?>

<h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>

<a href="/categories/create" class="btn btn-primary mb-3">Добавить категорию</a>

<?php if (empty($categories)): ?>
  <p>Категорий нет.</p>
<?php else: ?>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $cat): ?>
        <tr>
          <td><?= (int)$cat['id'] ?></td>
          <td><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <a href="/categories/<?= $cat['id'] ?>/edit"
               class="btn btn-sm btn-secondary">Правка</a>
            <form action="/categories/<?= $cat['id'] ?>/delete"
                  method="post" style="display:inline">
              <button class="btn btn-sm btn-danger">Удалить</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
