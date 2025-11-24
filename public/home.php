<?php

require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';

$userController = new UserController();
$userController->showUser(1);
