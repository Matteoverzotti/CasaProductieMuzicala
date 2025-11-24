<?php
/* @var array $user */
?>

<h2>User <?php echo htmlspecialchars($user['username']); ?></h2>

<ul>
    <li>id: <?php echo htmlspecialchars($user['id']); ?></li>
    <li>Name: <?php echo htmlspecialchars($user['full_name']); ?></li>
    <li>Email: <?php echo htmlspecialchars($user['email']); ?></li>
</ul>
