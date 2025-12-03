<?php
namespace App\Controllers;

use PDO;
use App\Models\User;
use App\Helpers\Flash;

class AuthController
{
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    /** Показывает форму регистрации */
    public function showRegister(): void
    {
        require __DIR__ . '/../Views/auth/register.php';
    }

    /** Обрабатывает форму регистрации */
    public function register(): void
    {
        $email = trim($_POST['email'] ?? '');
        $name  = trim($_POST['name'] ?? '');
        $pass1 = $_POST['password'] ?? '';
        $pass2 = $_POST['password_confirm'] ?? '';

        // Валидация
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('error', 'Некорректный email.');
        }
        if (mb_strlen($pass1) < 6) {
            Flash::set('error', 'Пароль должен быть не короче 6 символов.');
        }
        if ($pass1 !== $pass2) {
            Flash::set('error', 'Пароли не совпадают.');
        }
        if ($this->userModel->findByEmail($email)) {
            Flash::set('error', 'Пользователь с таким email уже существует.');
        }

        // Если есть ошибки — вернуть форму
        $errors = Flash::get()['error'] ?? [];
        if ($errors) {
            // Чтобы форма сохранила введённые данные:
            $old = ['email'=>$email,'name'=>$name];
            require __DIR__ . '/../Views/auth/register.php';
            return;
        }

        // Хешируем пароль и сохраняем
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $userId = $this->userModel->create([
            'email'         => $email,
            'password_hash' => $hash,
            'name'          => $name,
        ]);

        Flash::set('success', 'Регистрация прошла успешно. Пожалуйста, войдите.');
        header('Location: /login');
        exit;
    }

    /** Показывает форму входа */
    public function showLogin(): void
    {
        require __DIR__ . '/../Views/auth/login.php';
    }

    /** Обрабатывает форму входа */
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($pass, $user['password_hash'])) {
            Flash::set('error', 'Неверный email или пароль.');
            require __DIR__ . '/../Views/auth/login.php';
            return;
        }

        // Успешно: сохраняем в сессии
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? $user['email'];
        $_SESSION['is_admin']  = (bool)$user['is_admin'];
        Flash::set('success', 'Вы успешно вошли в систему.');

        header('Location: /');
        exit;
    }

    /** Выход (очистка сессии) */
    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}
