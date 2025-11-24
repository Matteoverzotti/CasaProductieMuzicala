<?php
/* @var User|null $user */

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
 
        <?php 
            if (!empty($user)):
        ?>
            <p>Bine ai venit, <?= htmlspecialchars($user->username) ?>!</p>
            <p>
                <a href="/profile">Profilul meu</a> | 
                <a href="/edit-profile">Editează profilul</a> | 
                <a href="/logout">Deconectare</a>
            </p>
        <?php else: ?>
            <p><a href="/login">Conectare</a> sau <a href="/register">Înregistrare</a></p>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div>
                Contul a fost șters cu succes.
            </div>
        <?php endif; ?>
    </body>
</html>

