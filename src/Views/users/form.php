<?php
$title = isset($user['id'])
    ? 'Редактирование пользователя'
    : 'Новый пользователь';
require __DIR__ . '/../layout/header.php';
?>

<h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>

<form action="/admin/users/<?= $user['id'] ?>" method="post">
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" id="email" name="email" class="form-control"
           value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>"
           readonly>
  </div>
  <div class="mb-3">
    <label for="name" class="form-label">Имя</label>
    <input type="text" id="name" name="name" class="form-control" required
           value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </div>
  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" value="1"
           id="is_admin" name="is_admin"
           <?= !empty($user['is_admin']) ? 'checked' : '' ?>>
    <label class="form-check-label" for="is_admin">
      Администратор
    </label>
  </div>
  <button type="submit" class="btn btn-success">Сохранить</button>
  <a href="/admin/users" class="btn btn-secondary">Отмена</a>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
