<?php
    /**
     * @var array $employees
     */

    $producers = array_filter($employees, function($emp) {
        return $emp['role_id'] === PRODUCER_ROLE_ID;
    });
    $sound_engineers = array_filter($employees, function($emp) {
        return $emp['role_id'] === SOUND_ENGINEER_ROLE_ID;
    });
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Angajați Activi - Casa de Producție</title>
</head>
<body>
    <div>
        <a href="/">← Înapoi la pagina principală</a>
    </div>

    <h2>Echipa Noastră</h2>

    <?php if (empty($employees)): ?>
        <p>Momentan nu există angajați activi.</p>
    <?php else: ?>
        <h2>Producerii noștri:</h2>
        <?php foreach ($producers as $prod): ?>
            <div>
                <h3><?= htmlspecialchars($prod['full_name']) ?></h3>
                <p>This is a sample bio</p>
            </div>
            <hr>
        <?php endforeach; ?>

        <h2>Inginerii noștri de sunet:</h2>
        <?php foreach ($sound_engineers as $eng): ?>
            <div>
                <h3><?= htmlspecialchars($eng['full_name']) ?></h3>
                <p>This is a sample bio</p>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
