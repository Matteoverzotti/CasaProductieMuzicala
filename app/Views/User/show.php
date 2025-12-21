<?php
/* @var User $user */
require_once __DIR__ . '/../../Models/User.php';
require_once __DIR__ . '/../../../middleware/Auth.php';

$currentUser = Auth::user();
$isOwnProfile = $isOwnProfile ?? ($currentUser && $currentUser->id === $user->id);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilizator - Casa de Producție</title>
</head>
<body>
    <div>
        <a href="/">← Înapoi la pagina principală</a>
    </div>

    <h2>Profil Utilizator: <?= htmlspecialchars($user->username); ?></h2>

    <?php if (!empty($flash)): ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </p>
    <?php endif; ?>

    <div>
        <ul>
            <li><strong>ID:</strong> <?= htmlspecialchars($user->id); ?></li>
            <li><strong>Nume complet:</strong> <?= htmlspecialchars($user->full_name); ?></li>
            <li><strong>Email:</strong> <?= htmlspecialchars($user->email); ?></li>
            <?php if ($user->created_at): ?>
                <li><strong>Înregistrat la:</strong> <?= htmlspecialchars($user->created_at); ?></li>
            <?php endif; ?>
        </ul>
    </div>

    <?php if ($isOwnProfile || ($currentUser && $currentUser->role_id === ADMIN_ROLE_ID)): ?>
        <div>
            <a href="/edit-profile<?= $isOwnProfile ? '' : '?id=' . $user->id ?>">Editează Profilul</a>
        </div>
    <?php endif; ?>
</body>
</html>
