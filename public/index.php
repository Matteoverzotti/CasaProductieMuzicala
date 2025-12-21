<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';
require_once __DIR__ . '/../middleware/Auth.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Simple routing mechanism

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);

$routes = [
    '/' => [HomeController::class, 'index'],
    '/login' => [AuthController::class, 'login'],
    '/register' => [AuthController::class, 'register'],
    '/logout' => [AuthController::class, 'logout'],
    '/profile' => [UserController::class, 'showUserProfile'],
    '/edit-profile' => [UserController::class, 'editProfile'],
    '/delete-account' => [UserController::class, 'deleteAccount'],
    '/users' => [UserController::class, 'showAllUsers'],
];

if (array_key_exists($request, $routes)) {
    $controllerClass = $routes[$request][0];
    $controller = new $controllerClass();
    $method = $routes[$request][1];
    $controller->$method();
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>The page you are looking for does not exist.</p>";
}
