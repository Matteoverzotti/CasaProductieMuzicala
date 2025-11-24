<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Controllers/HomeController.php';
require __DIR__ . '/../app/Controllers/AuthController.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Simple routing mechanism

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);

$routes = [
    '/' => [HomeController::class, 'index'],
    '/login' => [AuthController::class, 'login'],
    '/register' => [AuthController::class, 'register'],
    '/logout' => [AuthController::class, 'logout' ],
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
