<?php
/**
 * @var Album $album
 * @var array $tracks
 * @var string $artist_name
 * @var string $csrf_token
 */
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($album->title ?? 'Album') ?> - Casa de Producție</title>
</head>
<body>
    <div>
        <a href="/">← Înapoi la pagina principală</a> |
        <a href="/music">← Înapoi la muzică</a>
    </div>

    <?php if (!empty($flash)): ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($album)): ?>
        <p style="color: red">Albumul nu a fost găsit.</p>
    <?php else: ?>
        <h1><?= htmlspecialchars($album->title) ?></h1>
        
        <div>
            <?php if (!empty($artist_name)): ?>
                <p><strong>Artist:</strong> <?= htmlspecialchars($artist_name) ?></p>
            <?php endif; ?>
            <?php if (!empty($album->genre)): ?>
                <p><strong>Gen:</strong> <?= htmlspecialchars($album->genre) ?></p>
            <?php endif; ?>
            <?php if (!empty($album->release_date)): ?>
                <p><strong>Data lansării:</strong> <?= htmlspecialchars($album->release_date) ?></p>
            <?php endif; ?>
        </div>

        <hr>

        <h2>Piese</h2>
        <?php if (empty($tracks)): ?>
            <p>Nu există piese în acest album.</p>
        <?php else: ?>
            <ol>
                <?php foreach ($tracks as $track): ?>
                    <li>
                        <strong><?= htmlspecialchars($track['title']) ?></strong>
                        <?php if (!empty($track['duration'])): ?>
                            - <?= floor($track['duration'] / 60) ?>:<?= str_pad($track['duration'] % 60, 2, '0', STR_PAD_LEFT) ?>
                        <?php endif; ?>
                        <?php if (!empty($track['status'])): ?>
                            <span style="color: <?= $track['status'] === 'released' ? 'green' : 'orange' ?>">
                                (<?= htmlspecialchars($track['status']) ?>)
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>