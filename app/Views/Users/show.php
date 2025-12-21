<?php
require_once __DIR__ . '/../../Models/User.php';
require_once __DIR__ . '/../../../middleware/Auth.php';

$users = User::allUsers();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toți utilizatorii</title>
</head>
<body>
    <div>
        <a href="/">← Înapoi la pagina principală</a>
    </div>

    <?php if (!empty($flash)): ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </p>
    <?php endif; ?>

    <ul>
        <?php foreach ($users as $user) : ?>
            <li>
                <strong>ID:</strong> <?= htmlspecialchars($user->id); ?>,
                <strong>Username:</strong> <?= htmlspecialchars($user->username); ?>,
                <strong>Nume complet:</strong> <?= htmlspecialchars($user->full_name); ?>,
                <strong>Email:</strong> <?= htmlspecialchars($user->email); ?>
                <?php if ($user->created_at): ?>
                    , <strong>Înregistrat la:</strong> <?= htmlspecialchars($user->created_at); ?>
                <?php endif; ?>
                | <a href="/profile?id=<?= $user->id ?>">Vezi</a>
                | <a href="/edit-profile?id=<?= $user->id ?>">Editează</a>
                | <a href="/delete-account?id=<?= $user->id ?>">Șterge</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
