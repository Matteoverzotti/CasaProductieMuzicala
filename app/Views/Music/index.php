<?php
/**
 * @var array $albums
 * @var string $csrf_token
 */
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muzică Lansată - Casa de Producție</title>
</head>
<body>
    <div>
        <a href="/">← Înapoi la pagina principală</a>
    </div>

    <h1>Muzică Lansată</h1>

    <?php if (!empty($flash)): ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($albums)): ?>
        <p>Momentan nu există albume lansate.</p>
    <?php else: ?>
        <h2>Albume disponibile</h2>
        <ul>
            <?php foreach ($albums as $album): ?>
                <li>
                    <strong><?= htmlspecialchars($album['title']) ?></strong>
                    <?php if (!empty($album['artist_name'])): ?>
                        - de <?= htmlspecialchars($album['artist_name']) ?>
                    <?php endif; ?>
                    <?php if (!empty($album['genre'])): ?>
                        (<?= htmlspecialchars($album['genre']) ?>)
                    <?php endif; ?>
                    <?php if (!empty($album['release_date'])): ?>
                        | Lansat: <?= htmlspecialchars($album['release_date']) ?>
                    <?php endif; ?>
                    <br>
                    <a href="/music/album?id=<?= htmlspecialchars($album['id']) ?>">Vezi detalii →</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>