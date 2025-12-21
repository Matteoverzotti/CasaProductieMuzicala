<?php
/* @var Message[] $messages */
/* @var User $user */

$activeMessages = array_filter($messages, fn($m) => !$m->is_archived);
$archivedMessages = array_filter($messages, fn($m) => $m->is_archived);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mesaje Primite</title>
</head>
<body>
    <header>
        <h1>Mesaje Primite</h1>
        <nav>
            <a href="/">← Înapoi la pagina principală </a>
        </nav>
    </header>

    <main>
        <?php if (!empty($flash)): ?>
            <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </p>
        <?php endif; ?>

        <?php if (empty($messages)): ?>
            <p>Nu există mesaje primite.</p>
        <?php else: ?>
            
            <?php if (!empty($activeMessages)): ?>
                <section>
                    <header>
                        <h2>Mesaje Active</h2>
                        <hr>
                    </header>
                    <?php foreach ($activeMessages as $message): ?>
                        <article>
                            <header>
                                <h3><?= htmlspecialchars($message->subject) ?></h3>
                                <p><strong>De la:</strong> <?= htmlspecialchars($message->sender_name) ?> (<?= htmlspecialchars($message->sender_email) ?>)</p>
                            </header>
                            <pre><?= htmlspecialchars($message->body) ?></pre>
                            <footer>
                                <p>
                                    <small>Trimis la: <?= $message->sent_at ?></small>
                                </p>
                                <form action="/admin/messages/archive" method="POST">
                                    <input type="hidden" name="id" value="<?= $message->id ?>">
                                    <button type="submit"><strong>Arhivează</strong></button>
                                </form>
                            </footer>
                            <hr>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <?php if (!empty($archivedMessages)): ?>
                <section>
                    <header>
                        <h2>Mesaje Arhivate</h2>
                        <hr>
                    </header>
                    <?php foreach ($archivedMessages as $message): ?>
                        <article style="opacity: 0.5">
                            <header>
                                <h3><del><?= htmlspecialchars($message->subject) ?></del></h3>
                                <p><small>De la: <?= htmlspecialchars($message->sender_name) ?> (<?= htmlspecialchars($message->sender_email) ?>)</small></p>
                            </header>
                            <pre><?= htmlspecialchars($message->body) ?></pre>
                            <footer>
                                <p>
                                    <small>Trimis la: <?= $message->sent_at ?> [Arhivat]</small>
                                </p>
                                <form action="/admin/messages/dearchive" method="POST">
                                    <input type="hidden" name="id" value="<?= $message->id ?>">
                                    <button type="submit">Dezarhivează</button>
                                </form>
                            </footer>
                            <hr>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

        <?php endif; ?>
    </main>

    <footer>
        <p><a href="/">Înapoi la Home</a></p>
    </footer>
</body>
</html>
