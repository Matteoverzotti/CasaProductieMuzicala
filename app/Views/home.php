<?php
/* @var User|null $user
 * @var string|null $error
 */

    require_once __DIR__ . '/../Models/User.php';
    require_once __DIR__ . '/../Controllers/UserController.php';
    require_once __DIR__ . '/../Controllers/HomeController.php';

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0
            ">
        <title>Home</title>
    </head>
    <body>
        <h1>Casă de producție - Home</h1>

        <?php if (!empty($flash)): ?>
            <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </p>
        <?php endif; ?>

        <?php
            if (!empty($user)):
        ?>
            <p>Bine ai venit, <?= htmlspecialchars($user->username) ?>!</p>
            <p>
                <a href="/profile">Profilul meu</a> |
                <a href="/edit-profile">Editează profilul</a> |
                <a href="/employees">Vezi echipa noastră</a> |
                <?= $user->role_id !== ADMIN_ROLE_ID ? '<a href="/contact">Contact</a> | ' : '' ?>
                <?= $user->role_id === ADMIN_ROLE_ID ? '<a href="/users">Vezi toți utilizatorii</a> |' : '' ?>
                <?= $user->role_id === ADMIN_ROLE_ID ? '<a href="/admin/messages">Vezi mesaje</a> |' : '' ?>
                <a href="/logout">Deconectare</a>
            </p>
        <?php else: ?>
            <p><a href="/login">Conectare</a> sau <a href="/register">Înregistrare</a></p>
            <h2>Bine ai venit!</h2>
            <p>Aceasta este pagina principală a aplicației pentru gestionarea unei case de producție muzicală.
                Deocamdată nu ești autentificat/ă, dar îți poți face cont accesând linkurile de mai sus.</p>
            <p>Dacă vrei să descoperi <a href="/employees">echipa noastră</a>, creează-ți un cont și hai să colaborăm!</p>
        <?php endif; ?>
    </body>
</html>

