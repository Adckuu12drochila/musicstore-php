<?php
$title = 'Управление пользователями';
require __DIR__ . '/../layout/header.php';
?>

<h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>

<?php if (empty($users)): ?>
  <p>Нет пользователей.</p>
<?php else: ?>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Имя</th>
        <th>Админ</th>
        <th>Дата регистрации</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= $u['is_admin'] ? 'Да' : 'Нет' ?></td>
          <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($u['created_at']))) ?></td>
          <td>
            <a href="/admin/users/<?= $u['id'] ?>/edit"
               class="btn btn-sm btn-outline-secondary">Правка</a>
            <form action="/admin/users/<?= $u['id'] ?>/delete"
                  method="post" style="display:inline">
              <button class="btn btn-sm btn-outline-danger"
                      onclick="return confirm('Удалить пользователя?');">
                Удалить
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
