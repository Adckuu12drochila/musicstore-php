<?php

namespace App\Controllers;

use PDO;
use App\Models\Category;
use App\Helpers\Flash;

class CategoryController
{
    private Category $categoryModel;

    public function __construct(PDO $pdo)
    {
        $this->categoryModel = new Category($pdo);
    }

    /**
     * Список категорий
     */
    public function index(): void
    {
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../Views/categories/index.php';
    }

    /**
     * Форма создания категории
     */
    public function create(): void
    {
        $category = null;
        require __DIR__ . '/../Views/categories/form.php';
    }

    /**
     * Сохранение новой категории
     */
    public function store(): void
    {
        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        // Валидация
        $errors = $this->validateCategory($data);
        if ($errors) {
            foreach ($errors as $err) {
                Flash::set('error', $err);
            }
            // Передаём данные обратно в форму, чтобы сохранить ввод
            $category = $data;
            require __DIR__ . '/../Views/categories/form.php';
            return;
        }

        // Сохранение
        $this->categoryModel->create($data);
        Flash::set('success', 'Категория успешно создана.');

        header('Location: /categories');
        exit;
    }

    /**
     * Форма редактирования категории
     */
    public function edit(int $id): void
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            http_response_code(404);
            echo 'Категория не найдена';
            return;
        }
        require __DIR__ . '/../Views/categories/form.php';
    }

    /**
     * Обновление категории
     */
    public function update(int $id): void
    {
        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ];

        // Валидация
        $errors = $this->validateCategory($data);
        if ($errors) {
            foreach ($errors as $err) {
                Flash::set('error', $err);
            }
            // Сливаем старые данные с новыми, чтобы заполнить форму
            $category = array_merge(
                $this->categoryModel->find($id) ?? [],
                $data
            );
            require __DIR__ . '/../Views/categories/form.php';
            return;
        }

        // Сохранение изменений
        $this->categoryModel->update($id, $data);
        Flash::set('success', 'Категория успешно обновлена.');

        header('Location: /categories');
        exit;
    }

    /**
     * Удаление категории
     */
    public function delete(int $id): void
    {
        $this->categoryModel->delete($id);
        Flash::set('success', 'Категория успешно удалена.');

        header('Location: /categories');
        exit;
    }

    /**
     * Валидация данных категории
     *
     * @param array $data
     * @return string[] Массив сообщений об ошибках
     */
    private function validateCategory(array $data): array
    {
        $errors = [];

        if (mb_strlen($data['name']) < 3) {
            $errors[] = 'Название категории должно быть минимум 3 символа.';
        }

        return $errors;
    }
}
