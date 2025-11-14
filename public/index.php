<?php

declare(strict_types=1);

// autoload
require __DIR__ . '/../vendor/autoload.php';

// tiny .env loader (reads .env into getenv and $_ENV)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$key, $val] = array_map('trim', explode('=', $line, 2) + [1=>null]);
        if ($key && $val !== null) {
            putenv("$key=$val");
            $_ENV[$key] = $val;
        }
    }
}

use App\Core\Router;
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;

Session::start();

$router = new Router();
$auth = new AuthController();
$dash = new DashboardController();

$router->get('/', fn() => $auth->showLogin());
$router->get('/login', fn() => $auth->showLogin());
$router->get('/register', fn() => $auth->showRegister());
$router->get('/dashboard', fn() => $dash->index());

$router->post('/register', fn() => $auth->register());
$router->post('/login', fn() => $auth->login());
$router->post('/post', fn() => $dash->createPost());
$router->get('/logout', fn() => $auth->logout());
$router->get('/dashboard', fn() => $dash->index());
$router->post('/post', fn() => $dash->createPost());
$router->post('/post/like', fn() => $dash->toggleLike());

$router->get('/post/edit', fn() => $dash->editForm());
$router->post('/post/edit', fn() => $dash->editPost());

$router->get('/post/edit', fn() => $dash->showEditForm());
$router->post('/post/edit', fn() => $dash->updatePost());


$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
