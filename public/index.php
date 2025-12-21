<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';
require_once __DIR__ . '/../app/Controllers/ContactController.php';
require_once __DIR__ . '/../middleware/Auth.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Simple routing mechanism

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);

$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/login' => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/logout' => [AuthController::class, 'logout'],
        '/profile' => [UserController::class, 'showUserProfile'],
        '/edit-profile' => [UserController::class, 'editProfile'],
        '/users' => [UserController::class, 'showAllUsers'],
        '/contact' => [ContactController::class, 'contact'],
        '/admin/messages' => [ContactController::class, 'adminMessages'],
        '/delete-account' => [UserController::class, 'deleteAccount'],
    ],
    'POST' => [
        '/login' => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/contact' => [ContactController::class, 'contact'],
        '/delete-account' => [UserController::class, 'deleteAccount'],
        '/edit-profile' => [UserController::class, 'editProfile'],
        '/admin/messages/archive' => [ContactController::class, 'archiveMessage'],
        '/admin/messages/dearchive' => [ContactController::class, 'dearchiveMessage'],
    ],
];

if (isset($routes[$method][$request])) {
    $controllerClass = $routes[$method][$request][0];
    $controller = new $controllerClass();
    $methodName = $routes[$method][$request][1];
    $controller->$methodName();
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>The page you are looking for does not exist or the method is not allowed.</p>";
}
