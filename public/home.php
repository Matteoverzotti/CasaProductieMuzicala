<?php

echo "Get user by id with id = 2<br>";
require_once __DIR__ . '/../app/Models/User.php';
$userModel = new User();
$user = $userModel->getUserById(2);
if ($user) {
    echo "User found: <br>";
    echo "ID: " . htmlspecialchars($user['id']) . "<br>";
    echo "Name: " . htmlspecialchars($user['full_name']) . "<br>";
    echo "Email: " . htmlspecialchars($user['email']) . "<br>";
} else {
    echo "User not found.";
}
