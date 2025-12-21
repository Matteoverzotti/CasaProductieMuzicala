<?php
/* @var User $user */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contactează Adminul</title>
</head>
<body>
    <h1>Contactează Adminul</h1>

    <?php if (!empty($flash)): ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </p>
    <?php endif; ?>

    <p>Bună, <?= htmlspecialchars($user->full_name ?: $user->username) ?> (<?= htmlspecialchars($user->email) ?>). Folosește formularul de mai jos pentru a trimite un mesaj administratorului.</p>

    <form action="/contact" method="POST">
        <fieldset>
            <legend>Mesaj nou</legend>
            <p>
                <label for="subject">Subiect:</label><br>
                <input type="text" id="subject" name="subject" required size="40">
            </p>
            <p>
                <label for="body">Mesaj:</label><br>
                <textarea id="body" name="body" rows="10" cols="50" required></textarea>
            </p>
            <p>
                <button type="submit">Trimite mesaj</button>
            </p>
        </fieldset>
    </form>

    <p><a href="/">Înapoi la Home</a></p>
</body>
</html>
