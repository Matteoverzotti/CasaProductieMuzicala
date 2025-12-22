<?php
/* @var User|null $user
 * @var string|null $error
 * @var User[]|null $assignableUsers
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

            <?php if (in_array($user->role_id, [ARTIST_ROLE_ID, SOUND_ENGINEER_ROLE_ID, PRODUCER_ROLE_ID])): ?>
                <?php if (!empty($pendingProjects)): ?>
                    <h3>Invitații în proiecte</h3>
                    <ul>
                        <?php foreach ($pendingProjects as $project): ?>
                            <li>
                                <a href="/project/show?id=<?= $project->id ?>">
                                    <?= htmlspecialchars($project->title) ?>
                                </a>
                                <form action="/project/update-status" method="POST" style="display:inline;">
                                    <input type="hidden" name="project_id" value="<?= $project->id ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit">Aprobă</button>
                                </form>
                                <form action="/project/update-status" method="POST" style="display:inline;">
                                    <input type="hidden" name="project_id" value="<?= $project->id ?>">
                                    <input type="hidden" name="status" value="denied">
                                    <button type="submit">Refuză</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <h3>Proiectele mele</h3>
                <?php if (!empty($projects)): ?>
                    <ul>
                        <?php foreach ($projects as $project): ?>
                            <li>
                                <a href="/project/show?id=<?= $project->id ?>">
                                    <?= htmlspecialchars($project->title) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nu ești implicat în niciun proiect momentan.</p>
                <?php endif; ?>

                <h3>Creează un proiect nou</h3>
                <form action="/project/create" method="POST">
                    <div>
                        <label for="title">Titlu proiect:</label>
                        <input type="text" name="title" id="title" required>
                    </div>
                    <div>
                        <label>Atribuie utilizatori:</label><br>
                        <?php foreach ($assignableUsers as $u): ?>
                            <?php if ($u->id !== $user->id): ?>
                                <input type="checkbox" name="assigned_users[]" value="<?= $u->id ?>" id="user_<?= $u->id ?>">
                                <label for="user_<?= $u->id ?>"><?= htmlspecialchars($u->full_name) ?> (<?= htmlspecialchars($u->username) ?>)</label><br>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit">Creează proiect</button>
                </form>
            <?php endif; ?>

        <?php else: ?>
            <p><a href="/login">Conectare</a> sau <a href="/register">Înregistrare</a></p>
            <h2>Bine ai venit!</h2>
            <p>Aceasta este pagina principală a aplicației pentru gestionarea unei case de producție muzicală.
                Deocamdată nu ești autentificat/ă, dar îți poți face cont accesând linkurile de mai sus.</p>
            <p>Dacă vrei să descoperi <a href="/employees">echipa noastră</a>, creează-ți un cont și hai să colaborăm!</p>
        <?php endif; ?>
    </body>
</html>

