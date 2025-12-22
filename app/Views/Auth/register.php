<?php
/**
 * @var string|null $error
 * @var string $csrf_token
 */
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0
            ">
        <title>Register</title>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </head>
    <body>
        <h2>Înregistrare</h2>
        <?php if (!empty($flash)): ?>
            <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="/register">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <label>Username: <input type="text" name="username" required></label><br>
            <label>Nume complet: <input type="text" name="full_name" required></label><br>
            <label>Email: <input type="text" name="email" required></label><br>
            <label>Parola: <input type="password" name="password" required></label><br>
            <label>Repetă Parola: <input type="password" name="confirm_password" required></label><br>
            <div class="g-recaptcha" data-sitekey="<?= $_ENV['RECAPTCHA_SITE_KEY'] ?>"></div><br>
            <button>Înregistrează-te</button>
        </form>

        <p><a href="/login">Ai deja cont? Conectează-te</a></p>
        <p><a href="/">Înapoi</a></p>
    </body>
</html>
