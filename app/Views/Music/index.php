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
    <nav>
        <a href="/">← Înapoi la pagina principală</a>
    </nav>

    <h1>🎵 Muzică Lansată</h1>

    <?php if (!empty($flash)): ?>
        <p><?= htmlspecialchars($flash['message']) ?></p>
    <?php endif; ?>

    <?php if (empty($albums)): ?>
        <p>Momentan nu există albume lansate.</p>
    <?php else: ?>
        <h2>📀 Albume disponibile</h2>
        
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Album</th>
                    <th>Artist</th>
                    <th>Gen</th>
                    <th>Data lansării</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($albums as $album): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($album['title']) ?></strong></td>
                        <td><?= !empty($album['artist_name']) ? htmlspecialchars($album['artist_name']) : '<em>Necunoscut</em>' ?></td>
                        <td><?= !empty($album['genre']) ? htmlspecialchars($album['genre']) : '-' ?></td>
                        <td><?= !empty($album['release_date']) ? htmlspecialchars(date('d M Y', strtotime($album['release_date']))) : '-' ?></td>
                        <td><a href="/music/album?id=<?= htmlspecialchars($album['id']) ?>">Vezi detalii →</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p>Total: <?= count($albums) ?> album<?= count($albums) !== 1 ? 'e' : '' ?></p>
    <?php endif; ?>
</body>
</html>