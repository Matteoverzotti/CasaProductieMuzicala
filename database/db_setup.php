<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$dsn = $_ENV['DB_DSN'] ?? '';
$user = $_ENV['DB_USER'] ?? '';
$pass = $_ENV['DB_PASS'] ?? '';

if (empty($dsn)) {
    die("Error: DB_DSN not found in .env file.\n");
}

try {
    // Connect to the database
    preg_match('/dbname=([^;]+)/', $dsn, $matches);
    $dbname = $matches[1] ?? null;

    if (!$dbname) {
        die("Error: Could not determine database name from DSN.\n");
    }

    $dsnWithoutDb = preg_replace('/dbname=[^;]+;?/', '', $dsn);
    $pdo = new PDO($dsnWithoutDb, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Connected to database server.\n";

    echo "Resetting database: $dbname...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname` ");
    $pdo->exec("CREATE DATABASE `$dbname` ");
    $pdo->exec("USE `$dbname` ");

    echo "Database `$dbname` created.\n";

    $migrations = [
        'console.sql',
        'roles_setup.sql'
    ];

    foreach ($migrations as $file) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            echo "Executing $file...\n";
            $sql = file_get_contents($path);
            $pdo->exec($sql);
            echo "Successfully executed $file.\n";
        } else {
            echo "Warning: $file not found at $path\n";
        }
    }

    echo "Database reset complete!\n";

    // Seed users for each role
    echo "Seeding users for each role...\n";
    $stmt = $pdo->query("SELECT id, name FROM role");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($roles as $role) {
        $roleId = $role['id'];
        $roleName = $role['name'];
        $username = strtolower(str_replace(' ', '_', $roleName));
        $email = $username . "@example.com";
        $password = "password123";
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $fullName = $roleName . " User";

        $checkStmt = $pdo->prepare("SELECT id FROM user WHERE username = ?");
        $checkStmt->execute([$username]);
        if (!$checkStmt->fetch()) {
            $insertStmt = $pdo->prepare("INSERT INTO user (role_id, username, email, password_hash, full_name) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->execute([$roleId, $username, $email, $passwordHash, $fullName]);
            echo "Created user: $username (Role: $roleName, Password: $password)\n";
        } else {
            echo "User $username already exists, skipping.\n";
        }
    }

    echo "Seeding complete!\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
}
