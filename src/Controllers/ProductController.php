<?php

namespace App\Controllers;

use PDO;
use Exception;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\Flash;

class ProductController
{
    private Product $productModel;
    private Category $categoryModel;
    private string $uploadDir;

    public function __construct(PDO $pdo)
    {
        $this->productModel  = new Product($pdo);
        $this->categoryModel = new Category($pdo);

        // Директория для хранения загруженных изображений
        $this->uploadDir = __DIR__ . '/../../public/uploads/products/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Список продуктов с пагинацией, поиском и сортировкой
     */
    public function index(): void
    {
        // Параметры фильтра
        $catParam   = $_GET['category_id'] ?? null;
        $categoryId = is_numeric($catParam) ? (int)$catParam : null;

        $search = trim($_GET['q'] ?? '');

        // Параметр сортировки
        $sort = $_GET['sort'] ?? '';

        // Параметры пагинации
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 6;
        $offset = ($page - 1) * $limit;

        // Всего элементов для расчёта страниц
        $total      = $this->productModel->count($categoryId, $search);
        $totalPages = (int)ceil($total / $limit);

        // Получаем список продуктов
        $products   = $this->productModel->all(
            $limit,
            $offset,
            $categoryId,
            $search,
            $sort
        );

        // Список категорий для фильтра
        $categories = $this->categoryModel->all();
        $breadcrumbs = [
          ['label'=>'Главная', 'url'=>'/'],
          ['label'=>'Каталог товаров', 'url'=>'/products']
        ];
        // Передаём в вид: товары, категории, фильтры и пагинацию
        require __DIR__ . '/../Views/products/index.php';
    }

    /**
     * Форма создания продукта
     */
    public function create(): void
    {
        $categories = $this->categoryModel->all();
        $product    = null;
        require __DIR__ . '/../Views/products/form.php';
    }

    /**
     * Сохранение нового продукта
     */
    public function store(): void
    {
        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => (float)($_POST['price'] ?? 0),
            'stock'       => (int)($_POST['stock'] ?? 0),
        ];

        // Обработка загрузки изображения
        if (!empty($_FILES['image']['name'])) {
            try {
                $filename = $this->handleImageUpload($_FILES['image']);
                $data['image_url'] = '/uploads/products/' . $filename;
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage());
                $data['image_url'] = null;
            }
        } else {
            $data['image_url'] = null;
        }

        // Валидация
        $errors = $this->validateProduct($data);
        if ($errors) {
            foreach ($errors as $err) {
                Flash::set('error', $err);
            }
            $categories = $this->categoryModel->all();
            $product    = $data; // чтобы форма сохранила введённые значения
            require __DIR__ . '/../Views/products/form.php';
            return;
        }

        // Сохранение
        $this->productModel->create($data);
        Flash::set('success', 'Продукт успешно создан.');

        header('Location: /products');
        exit;
    }

    /**
     * Форма редактирования продукта
     */
    public function edit(int $id): void
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            http_response_code(404);
            echo 'Продукт не найден';
            return;
        }
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../Views/products/form.php';
    }

    /**
     * Обновление продукта
     */
    public function update(int $id): void
    {
        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => (float)($_POST['price'] ?? 0),
            'stock'       => (int)($_POST['stock'] ?? 0),
        ];

        // Загрузка нового изображения, если есть
        if (!empty($_FILES['image']['name'])) {
            try {
                $filename = $this->handleImageUpload($_FILES['image']);
                $data['image_url'] = '/uploads/products/' . $filename;
            } catch (Exception $e) {
                Flash::set('error', $e->getMessage());
                // сохраняем старое изображение на случай ошибки
                $existing = $this->productModel->find($id);
                $data['image_url'] = $existing['image_url'] ?? null;
            }
        } else {
            $existing = $this->productModel->find($id);
            $data['image_url'] = $existing['image_url'] ?? null;
        }

        // Валидация
        $errors = $this->validateProduct($data);
        if ($errors) {
            foreach ($errors as $err) {
                Flash::set('error', $err);
            }
            $categories = $this->categoryModel->all();
            $product    = array_merge($this->productModel->find($id) ?: [], $data);
            require __DIR__ . '/../Views/products/form.php';
            return;
        }

        // Сохранение изменений
        $this->productModel->update($id, $data);
        Flash::set('success', 'Продукт успешно обновлён.');

        header('Location: /products');
        exit;
    }

    /**
     * Удаление продукта
     */
    public function delete(int $id): void
    {
        $product = $this->productModel->find($id);
        if ($product && !empty($product['image_url'])) {
            $path = __DIR__ . '/../../public' . $product['image_url'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $this->productModel->delete($id);
        Flash::set('success', 'Продукт удалён.');

        header('Location: /products');
        exit;
    }

    /**
     * Валидация данных продукта
     *
     * @param array $data
     * @return string[] Список сообщений об ошибках
     */
    private function validateProduct(array $data): array
    {
        $errors = [];

        if ($data['category_id'] <= 0) {
            $errors[] = 'Нужно выбрать категорию.';
        }
        if (mb_strlen($data['name']) < 3) {
            $errors[] = 'Название должно быть минимум 3 символа.';
        }
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            $errors[] = 'Цена должна быть числом больше 0.';
        }
        if (!is_int($data['stock']) || $data['stock'] < 0) {
            $errors[] = 'Остаток должен быть целым числом ≥ 0.';
        }

        return $errors;
    }

    /**
     * Обработка загрузки файла изображения
     *
     * @param array $file
     * @return string
     * @throws Exception
     */
    private function handleImageUpload(array $file): string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Ошибка при загрузке файла.');
        }
        if (!in_array($file['type'], $allowedTypes, true)) {
            throw new Exception('Недопустимый тип файла.');
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('prod_', true) . '.' . $ext;
        $dest     = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new Exception('Не удалось сохранить загруженный файл.');
        }

        return $filename;
    }
}
