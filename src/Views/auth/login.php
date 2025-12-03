<?php
$title = 'Вход';
require __DIR__ . '/../layout/header.php';

$email = $_POST['email'] ?? '';
?>
<h2>Вход</h2>

<form action="/login" method="post">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control"
           required value="<?= htmlspecialchars($email) ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Пароль</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary">Войти</button>
  <a href="/register" class="btn btn-link">Регистрация</a>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
