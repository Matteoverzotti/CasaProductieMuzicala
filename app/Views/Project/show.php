<?php
/**
 * @var Project $project
 * @var array $projectUsers
 * @var User $user
 * @var bool $isAuthor
 * @var string $csrf_token
 * @var array $files
 * @var int|null $parentId
 * @var ProjectFile|null $currentFolder
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proiect: <?= htmlspecialchars($project->title) ?></title>
    <style>
        table, td, th {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
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

        <div>
            <p><strong>Locație curentă: /<?= $currentFolder ? htmlspecialchars($currentFolder->filename) : '' ?>
                <?php if ($parentId): ?>
                    <a href="/project/show?id=<?= $project->id ?><?= $currentFolder->parent_id ? '&parent_id='.$currentFolder->parent_id : '' ?>"> Înapoi</a>
                <?php endif; ?>
                </strong>
                |
                <?php if ($parentId): ?>
                    <a href="/project/folder/download?folder_id=<?= $parentId ?>">Descarcă folderul curent (ZIP)</a>
                <?php else: ?>
                    <a href="/project/folder/download?project_id=<?= $project->id ?>">Descarcă tot proiectul (ZIP)</a>
                <?php endif; ?>
            </p>
        </div>


        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>Nume</th>
                    <th>Mărime</th>
                    <th>Încărcat de</th>
                    <th>Data încărcării</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($files)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Niciun fișier sau folder găsit.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($files as $f): ?>
                        <tr>
                            <td >
                                <?php if ($f['is_directory']): ?>
                                    📁 <a href="/project/show?id=<?= htmlspecialchars($project->id) ?>&parent_id=<?= htmlspecialchars($f['id']) ?>"><?= htmlspecialchars($f['filename']) ?></a>
                                <?php else: ?>
                                    📄 <?= htmlspecialchars($f['filename']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= $f['is_directory'] ? '-' : round($f['file_size'] / 1024, 2) . ' KB' ?></td>
                            <td><?= htmlspecialchars($f['uploader_name']) ?></td>
                            <td><?= htmlspecialchars($f['uploaded_at']) ?></td>
                            <td>
                                <?php if (!$f['is_directory']): ?>
                                    <a href="/project/file/download?file_id=<?= htmlspecialchars($f['id']) ?>">Descărcare</a>
                                <?php else: ?>
                                    <a href="/project/folder/download?folder_id=<?= htmlspecialchars($f['id']) ?>">Descărcare ZIP</a>
                                <?php endif; ?>
                                <form action="/project/file/delete" method="POST" style="display:inline;" onsubmit="return confirm('Sigur dorești să ștergi acest element?');">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="file_id" value="<?= htmlspecialchars($f['id']) ?>">
                                    <button type="submit" style="color: red;">Șterge</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <hr>

        <?php 
            $isApproved = false;
            foreach ($projectUsers as $pu) {
                if ($pu['id'] === $user->id && $pu['status'] === 'approved') {
                    $isApproved = true;
                    break;
                }
            }
        ?>
        <?php if ($isApproved): ?>
        <div style="display: flex; gap: 20px;">
            <div>
                <p><strong>Încarcă fișier</strong></p>
                <form action="/project/file/upload" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="project_id" value="<?= htmlspecialchars($project->id) ?>">
                    <input type="hidden" name="parent_id" value="<?= htmlspecialchars($parentId) ?>">
                    <input type="file" name="file" required>
                    <button type="submit">Încarcă</button>
                </form>
            </div>
            <div>
                <p><strong>Creează folder</strong></p>
                <form action="/project/folder/create" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="project_id" value="<?= htmlspecialchars($project->id) ?>">
                    <input type="hidden" name="parent_id" value="<?= htmlspecialchars($parentId) ?>">
                    <input type="text" name="folder_name" placeholder="Nume folder" required>
                    <button type="submit">Creează</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
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
