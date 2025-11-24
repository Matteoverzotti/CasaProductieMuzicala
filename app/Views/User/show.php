<?php
/* @var User $user */
require_once __DIR__ . '/../../Models/User.php';
?>

<h2>User <?= htmlspecialchars($user->username); ?></h2>

<ul>
    <li>id: <?= htmlspecialchars($user->id); ?></li>
    <li>Name: <?= htmlspecialchars($user->full_name); ?></li>
    <li>Email: <?= htmlspecialchars($user->email); ?></li>
</ul>
