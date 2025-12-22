<?php
/**
 * @var Project $project
 * @var array $projectUsers
 * @var User $user
 * @var bool $isAuthor
 * @var string $csrf_token
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proiect: <?= htmlspecialchars($project->title) ?></title>
</head>
<body>
    <a href="/">← Înapoi la pagina principală</a>

    <h1>Proiect: <?= htmlspecialchars($project->title) ?></h1>
    
    <?php if (isset($flash)): ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </p>
    <?php endif; ?>

    <p><strong>Status Proiect:</strong> <?= $project->end_date ? 'Finalizat' : 'In lucru' ?></p>
    <p><strong>Data începere:</strong> <?= htmlspecialchars($project->start_date) ?></p>

    <h3>Utilizatori implicați</h3>
    <ul>
        <?php foreach ($projectUsers as $u): ?>
            <li>
                <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?>) -
                <span style="color: <?= $u['status'] === 'pending' ? 'orange' : ($u['status'] === 'approved' ? 'green' : 'red') ?>">
                    <?= htmlspecialchars($u['status']) ?>
                </span>
                <?php if ($isAuthor && $u['status'] === 'denied'): ?>
                    <form action="/project/re-request" method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="project_id" value="<?= $project->id ?>">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button type="submit">Re-solicita aprobare</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div>
        <h3>Sandbox</h3>
        <p>Aici vei găsi atașate tot ce este nevoie pentru proiectul tău. Poți adăuga/șterge atașamente și publica piese.</p>
        <p>WIP</p>
    </div>

    <?php if ($isAuthor): ?>
        <hr>
        <form action="/project/delete" method="POST" onsubmit="return confirm('Sigur doresti sa stergi acest proiect?');">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="project_id" value="<?= $project->id ?>">
            <button type="submit" style="color: red;">Șterge Proiectul</button>
        </form>
    <?php endif; ?>

</body>
</html>
