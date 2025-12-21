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
            </li>
        <?php endforeach; ?>
    </ul>
<!--    <div>-->
<!--        <ul>-->
<!--            <li><strong>ID:</strong> --><?php //= htmlspecialchars($user->id); ?><!--</li>-->
<!--            <li><strong>Nume complet:</strong> --><?php //= htmlspecialchars($user->full_name); ?><!--</li>-->
<!--            <li><strong>Email:</strong> --><?php //= htmlspecialchars($user->email); ?><!--</li>-->
<!--            --><?php //if ($user->created_at): ?>
<!--                <li><strong>Înregistrat la:</strong> --><?php //= htmlspecialchars($user->created_at); ?><!--</li>-->
<!--            --><?php //endif; ?>
<!--        </ul>-->
<!--    </div>-->
<!---->
<!--    --><?php //if ($isOwnProfile): ?>
<!--        <div>-->
<!--            <a href="/edit-profile">Editează Profilul</a>-->
<!--        </div>-->
<!--    --><?php //endif; ?>
</body>
</html>
