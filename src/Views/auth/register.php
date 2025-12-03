<?php
$title = 'Регистрация';
require __DIR__ . '/../layout/header.php';

// старые данные, если были
$email = $old['email']   ?? '';
$name  = $old['name']    ?? '';
?>
<h2>Регистрация</h2>

<form action="/register" method="post">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control"
           required value="<?= htmlspecialchars($email) ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Имя (необязательно)</label>
    <input type="text" name="name" class="form-control"
           value="<?= htmlspecialchars($name) ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Пароль</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Подтвердите пароль</label>
    <input type="password" name="password_confirm" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
  <a href="/login" class="btn btn-link">Уже есть аккаунт?</a>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
