<?php
/**
 * @var User|null $user
 * @var string $csrf_token
 */
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formular Contact - Casa de Producție</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <header>
        <nav>
            <a href="/">← Înapoi la pagina principală</a>
        </nav>
        <h1>Formular Contact</h1>
    </header>

    <?php if (!empty($flash)): ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </p>
    <?php endif; ?>

    <?php if ($user): ?>
        <p>Bună, <?= htmlspecialchars($user->full_name ?: $user->username) ?>! Mesajul va fi trimis de pe adresa <strong><?= htmlspecialchars($user->email) ?></strong>.</p>
    <?php else: ?>
        <p>Te rugăm să ne transmiți informațiile de mai jos:</p>
    <?php endif; ?>

    <form action="/contact" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <?php if (!$user): ?>
            <fieldset>
                <legend>Informații contact</legend>
            
                <p>
                    <label for="name">Nume: <span style="color: red;">*</span></label><br>
                    <input type="text" id="name" name="name" required size="40" 
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        placeholder="Numele tău complet">
                </p>
                
                <p>
                    <label for="email">Email: <span style="color: red;">*</span></label><br>
                    <input type="email" id="email" name="email" required size="40"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="adresa@email.com">
                </p>
            </fieldset>
        <?php endif; ?>

        <fieldset>
            <legend>Mesaj</legend>
            
            <p>
                <label for="subject">Subiect: <span style="color: red;">*</span></label><br>
                <input type="text" id="subject" name="subject" required size="40"
                       value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                       placeholder="Subiectul mesajului">
            </p>
            
            <p>
                <label for="body">Mesaj: <span style="color: red;">*</span></label><br>
                <textarea id="body" name="body" rows="10" cols="50" required placeholder="Scrie mesajul tău aici..."><?= htmlspecialchars($_POST['body'] ?? '') ?></textarea>
            </p>
        </fieldset>

        <p>
            <div class="g-recaptcha" data-sitekey="<?= $_ENV['RECAPTCHA_SITE_KEY'] ?? '' ?>"></div>
        </p>
        
        <p>
            <button type="submit">Trimite mesaj</button>
        </p>
    </form>

    <?php if (!$user): ?>
        <p><small>Ai deja cont? <a href="/login">Conectează-te</a> pentru a completa automat datele tale.</small></p>
    <?php endif; ?>
</body>
</html>
