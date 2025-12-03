<?php
namespace App\Controllers;

use PDO;
use App\Models\User;
use App\Helpers\Flash;

class UserController
{
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    /** Админ: список всех пользователей */
    public function adminList(): void
    {
        $users = $this->userModel->all();
        require __DIR__ . '/../Views/users/index.php';
    }

    /** Админ: форма редактирования пользователя */
    public function edit(int $id): void
    {
        $user = $this->userModel->findById($id);
        if (!$user) {
            http_response_code(404);
            echo 'Пользователь не найден';
            return;
        }
        require __DIR__ . '/../Views/users/form.php';
    }

    /** Админ: сохраняет правки */
    public function update(int $id): void
    {
        $name     = trim($_POST['name'] ?? '');
        $isAdmin  = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';

        // Валидация
        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors[] = 'Имя должно быть минимум 2 символа.';
        }
        if ($errors) {
            foreach ($errors as $e) {
                Flash::set('error', $e);
            }
            $user = array_merge($this->userModel->findById($id) ?? [], [
                'name'     => $name,
                'is_admin' => $isAdmin,
            ]);
            require __DIR__ . '/../Views/users/form.php';
            return;
        }

        $this->userModel->update($id, ['name' => $name, 'is_admin' => $isAdmin]);
        Flash::set('success', 'Пользователь сохранён');
        header('Location: /admin/users');
        exit;
    }

    /** Админ: удаляет пользователя */
    public function delete(int $id): void
    {
        $this->userModel->delete($id);
        Flash::set('success', 'Пользователь удалён');
        header('Location: /admin/users');
        exit;
    }
}
