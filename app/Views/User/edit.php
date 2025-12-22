<?php
/* @var User $user */
/* @var bool $isAdmin */
/* @var bool $isOwnProfile */
require_once __DIR__ . '/../../Models/User.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editare Profil - Casa de Producție</title>
</head>
<body>
    <div>
        <a href="/">← Înapoi la pagina principală</a>
        <a href="/profile<?= $isOwnProfile ? '' : '?id=' . $user->id ?>">Vizualizare profil</a>
    </div>

    <h2>Editare Profil <?= $isOwnProfile ? '' : ' - ' . htmlspecialchars($user->username) ?></h2>

    <?php if (!empty($flash)): ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label for="username">Nume utilizator:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user->username) ?>" required>
        </div>

        <div>
            <label for="full_name">Nume complet:</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user->full_name) ?>" required>
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required>
        </div>

        <?php if ($isAdmin && !$isOwnProfile): ?>
            <div>
                <label for="role_id">Rol:</label>
                <select id="role_id" name="role_id">
                    <option value="<?= ADMIN_ROLE_ID ?>" <?= $user->role_id === ADMIN_ROLE_ID ? 'selected' : '' ?>>Admin</option>
                    <option value="<?= USER_ROLE_ID ?>" <?= $user->role_id === USER_ROLE_ID ? 'selected' : '' ?>>User</option>
                    <option value="<?= ARTIST_ROLE_ID ?>" <?= $user->role_id === ARTIST_ROLE_ID ? 'selected' : '' ?>>Artist</option>
                    <option value="<?= SOUND_ENGINEER_ROLE_ID ?>" <?= $user->role_id === SOUND_ENGINEER_ROLE_ID ? 'selected' : '' ?>>Sound Engineer</option>
                    <option value="<?= PRODUCER_ROLE_ID ?>" <?= $user->role_id === PRODUCER_ROLE_ID ? 'selected' : '' ?>>Producer</option>
                </select>
            </div>
        <?php endif; ?>

        <div>
            <label for="password">Parolă nouă (opțional):</label>
            <input type="password" id="password" name="password">
            <small>Lăsați gol dacă nu doriți să schimbați parola</small>
        </div>

        <div>
            <label for="confirm_password">Confirmați parola nouă:</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>

        <?php if ($isOwnProfile): ?>
            <div>
                <label for="current_password">Parola curentă (obligatorie):</label>
                <input type="password" id="current_password" name="current_password" required>
                <small>Necesară pentru confirmarea modificărilor</small>
            </div>
        <?php endif; ?>

        <button type="submit">Actualizează Profilul</button>
    </form>

    <?php if (!($isAdmin && $isOwnProfile)): ?>
    <div>
        <h3>Zonă Periculoasă</h3>
        <p>Ștergerea contului este o acțiune permanentă și nu poate fi anulată.</p>
        <a href="/delete-account<?= $isOwnProfile ? '' : '?id=' . $user->id ?>">Șterge Contul →</a>
    </div>
    <?php endif; ?>
</body>
</html>