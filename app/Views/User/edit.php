<?php
/* @var User $user */
/* @var string|null $error */
/* @var string|null $success */
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
        <a href="/profile">Vizualizare profil</a>
    </div>

    <h2>Editare Profil</h2>

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

        <div>
            <label for="password">Parolă nouă (opțional):</label>
            <input type="password" id="password" name="password">
            <small>Lăsați gol dacă nu doriți să schimbați parola</small>
        </div>

        <div>
            <label for="confirm_password">Confirmați parola nouă:</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>

        <div>
            <label for="current_password">Parola curentă (obligatorie):</label>
            <input type="password" id="current_password" name="current_password" required>
            <small>Necesară pentru confirmarea modificărilor</small>
        </div>

        <button type="submit">Actualizează Profilul</button>
    </form>

    <div>
        <h3>Zonă Periculoasă</h3>
        <p>Ștergerea contului este o acțiune permanentă și nu poate fi anulată.</p>
        <a href="/delete-account">Șterge Contul →</a>
    </div>
</body>
</html>