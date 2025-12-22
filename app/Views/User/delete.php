<?php
/**
 * @var User $user
 * @var bool $isOwnAccount
 * @var string $csrf_token
 */
require_once __DIR__ . '/../../Models/User.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ștergere Cont - Casa de Producție</title>
</head>
<body>
    <div>
        <a href="/">← Înapoi la pagina principală</a>
        <a href="/edit-profile<?= $isOwnAccount ? '' : '?id=' . $user->id ?>">← Înapoi la editare profil</a>
    </div>

    <h2>Ștergere Cont</h2>

    <?php if (!empty($flash)): ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </p>
    <?php endif; ?>

    <div>
        <strong>Atenție!</strong><br>
        Această acțiune va șterge permanent contul pentru utilizatorul <strong><?= htmlspecialchars($user->username) ?></strong>.<br>
        Toate datele asociate cu acest cont vor fi pierdute și nu pot fi recuperate.
    </div>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit" onclick="return confirm('Sunteți absolut sigur că doriți să ștergeți acest cont? Această acțiune nu poate fi anulată!')">
            Șterge Contul Permanent
        </button>
    </form>

    <a href="/edit-profile<?= $isOwnAccount ? '' : '?id=' . $user->id ?>">Anulează</a>
</body>
</html>