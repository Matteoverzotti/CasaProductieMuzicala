<?php

class Database {
    public static $pdo = null;

    public static function getConnection() : \PDO {
        if (self::$pdo) return self::$pdo;

        $dsn = $_ENV['DB_DSN'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $opts = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        self::$pdo = new \PDO($dsn, $username, $password, $opts);

        return self::$pdo;
    }
}
