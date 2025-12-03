<?php
declare(strict_types=1);
// public/index.php — Front Controller с маршрутизацией для всего приложения
ini_set('display_errors', '1');
error_reporting(E_ALL);


session_start();

// Заголовок и автозагрузка
header('Content-Type: text/html; charset=UTF-8');
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../db_connect.php'; // здесь создаётся $pdo

use App\Controllers\ProductController;
use App\Controllers\CategoryController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\OrderController;
use App\Controllers\UserController;
use App\Controllers\ProfileController;
use App\Controllers\PaymentController;
use App\Controllers\CouponController;

// Получаем URI и метод запроса
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// --- Маршруты для авторизации ---
if ($uri === '/register' && $method === 'GET') {
    (new AuthController($pdo))->showRegister();
    exit;
}
if ($uri === '/register' && $method === 'POST') {
    (new AuthController($pdo))->register();
    exit;
}
if ($uri === '/login' && $method === 'GET') {
    (new AuthController($pdo))->showLogin();
    exit;
}
if ($uri === '/login' && $method === 'POST') {
    (new AuthController($pdo))->login();
    exit;
}
if ($uri === '/logout') {
    (new AuthController($pdo))->logout();
    exit;
}

// --- Middleware: защита админских разделов ---
if (
    (preg_match('#^/cart#', $uri)
        || preg_match('#^/checkout#', $uri)
        || preg_match('#^/orders#', $uri)
        || preg_match('#^/payment#', $uri)
    )
    && empty($_SESSION['user_id'])
) {
    header('Location: /login');
    exit;
}
$adminOnly = [
    '#^/products/create#',
    '#^/products/\d+/edit#',
    '#^/products/\d+/delete#',
    '#^/categories/create#',
    '#^/categories/\d+/edit#',
    '#^/categories/\d+/delete#',
    '#^/admin/orders#',
    '#^/admin/users#',
    '#^/admin/orders#',
    '#^/admin/users#',
    '#^/admin/coupons#',
];
foreach ($adminOnly as $pattern) {
    if (preg_match($pattern, $uri) && empty($_SESSION['is_admin'])) {
        http_response_code(403);
        echo '<h1>403 — Доступ запрещён</h1>';
        exit;
    }
}

// Профиль
if ($uri === '/profile' && $method==='GET') {
    (new ProfileController($pdo))->show(); exit;
}
if ($uri === '/profile' && $method==='POST') {
    (new ProfileController($pdo))->update(); exit;
}
if ($uri === '/profile/password' && $method==='POST') {
    (new ProfileController($pdo))->changePassword(); exit;
}
// --- Маршруты для продуктов ---
if ($uri === '/products/create' && $method === 'GET') {
    (new ProductController($pdo))->create();
    exit;
}
if ($uri === '/products' && $method === 'POST') {
    (new ProductController($pdo))->store();
    exit;
}
if (preg_match('#^/products/(\d+)/edit$#', $uri, $m) && $method === 'GET') {
    (new ProductController($pdo))->edit((int)$m[1]);
    exit;
}
if (preg_match('#^/products/(\d+)$#', $uri, $m) && $method === 'POST') {
    (new ProductController($pdo))->update((int)$m[1]);
    exit;
}
if (preg_match('#^/products/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    (new ProductController($pdo))->delete((int)$m[1]);
    exit;
}
if ($uri === '/products' && $method === 'GET') {
    (new ProductController($pdo))->index();
    exit;
}

// --- Маршруты для категорий ---
if ($uri === '/categories/create' && $method === 'GET') {
    (new CategoryController($pdo))->create();
    exit;
}
if ($uri === '/categories' && $method === 'POST') {
    (new CategoryController($pdo))->store();
    exit;
}
if (preg_match('#^/categories/(\d+)/edit$#', $uri, $m) && $method === 'GET') {
    (new CategoryController($pdo))->edit((int)$m[1]);
    exit;
}
if (preg_match('#^/categories/(\d+)$#', $uri, $m) && $method === 'POST') {
    (new CategoryController($pdo))->update((int)$m[1]);
    exit;
}
if (preg_match('#^/categories/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    (new CategoryController($pdo))->delete((int)$m[1]);
    exit;
}
if ($uri === '/categories' && $method === 'GET') {
    (new CategoryController($pdo))->index();
    exit;
}

// --- Маршруты для корзины ---
if ($uri === '/cart' && $method === 'GET') {
    (new CartController($pdo))->show();
    exit;
}
if (preg_match('#^/cart/add/(\d+)$#', $uri, $m) && $method === 'POST') {
    (new CartController($pdo))->add((int)$m[1]);
    exit;
}
if (preg_match('#^/cart/remove/(\d+)$#', $uri, $m) && $method === 'POST') {
    (new CartController($pdo))->remove((int)$m[1]);
    exit;
}

// --- Маршруты для оформления заказа ---
if ($uri === '/checkout' && $method === 'GET') {
    (new OrderController($pdo))->showCheckout();
    exit;
}
if ($uri === '/checkout' && $method === 'POST') {
    (new OrderController($pdo))->processOrder();
    exit;
}
// --- Маршруты для истории заказов ---
if ($uri === '/orders' && $method === 'GET') {
    (new OrderController($pdo))->list();
    exit;
}

// --- Просмотр конкретного заказа ---
if (preg_match('#^/orders/(\d+)$#', $uri, $m) && $method === 'GET') {
    (new OrderController($pdo))->view((int)$m[1]);
    exit;
}

// --- Маршруты для оплаты по СБП (демо) ---
if (preg_match('#^/payment/sbp/(\d+)$#', $uri, $m) && $method === 'GET') {
    (new PaymentController($pdo))->showSbp((int)$m[1]);
    exit;
}

if (preg_match('#^/payment/sbp/(\d+)/status-json$#', $uri, $m) && $method === 'GET') {
    (new PaymentController($pdo))->statusJson((int)$m[1]);
    exit;
}

if (preg_match('#^/payment/sbp/(\d+)/simulate-success$#', $uri, $m) && $method === 'POST') {
    (new PaymentController($pdo))->simulateSbpSuccess((int)$m[1]);
    exit;
}

if (preg_match('#^/payment/sbp/(\d+)/restart$#', $uri, $m) && $method === 'POST') {
    (new PaymentController($pdo))->restartSbp((int)$m[1]);
    exit;
}

// Админ: список заказов
if ($uri === '/admin/orders' && $method === 'GET') {
    (new OrderController($pdo))->adminList();
    exit;
}

// Админ: обновление статуса
if (preg_match('#^/admin/orders/(\d+)/status$#', $uri, $m) && $method === 'POST') {
    (new OrderController($pdo))->updateStatus((int)$m[1]);
    exit;
}
// Админ: список пользователей
if ($uri === '/admin/users' && $method === 'GET') {
    (new UserController($pdo))->adminList();
    exit;
}

// Админ: форма редактирования
if (preg_match('#^/admin/users/(\d+)/edit$#', $uri, $m) && $method === 'GET') {
    (new UserController($pdo))->edit((int)$m[1]);
    exit;
}

// Админ: сохраняем изменения
if (preg_match('#^/admin/users/(\d+)$#', $uri, $m) && $method === 'POST') {
    (new UserController($pdo))->update((int)$m[1]);
    exit;
}

// Админ: удаление пользователя
if (preg_match('#^/admin/users/(\d+)/delete$#', $uri, $m) && $method === 'POST') {
    (new UserController($pdo))->delete((int)$m[1]);
    exit;
}

// Админ: купоны / промокоды
if ($uri === '/admin/coupons' && $method === 'GET') {
    (new CouponController($pdo))->adminIndex();
    exit;
}

if ($uri === '/admin/coupons' && $method === 'POST') {
    (new CouponController($pdo))->store();
    exit;
}

if (preg_match('#^/admin/coupons/(\d+)/toggle$#', $uri, $m) && $method === 'POST') {
    (new CouponController($pdo))->toggleActive((int)$m[1]);
    exit;
}

// Статические страницы: О нас, Контакты, Политика
if ($method === 'GET') {
    if ($uri === '/about') {
        require __DIR__ . '/../src/Views/static/about.php';
        exit;
    }
    if ($uri === '/contact') {
        require __DIR__ . '/../src/Views/static/contact.php';
        exit;
    }
    if ($uri === '/privacy') {
        require __DIR__ . '/../src/Views/static/privacy.php';
        exit;
    }
}

// --- По умолчанию — перенаправляем на каталог продуктов ---
if ($uri === '/' || $uri === '') {
    header('Location: /products');
    exit;
}

// --- 404 — Страница не найдена ---
http_response_code(404);
echo '<h1>404 — Страница не найдена</h1>';
