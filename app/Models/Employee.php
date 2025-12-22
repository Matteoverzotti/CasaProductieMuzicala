<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../Constants/constants.php';

class Employee extends Model {
    private static string $table = 'angajat';

    public int $id = 0;
    public int $user_id = 0;
    public int $salary = 0;
    public ?string $hire_date = null;
    public ?string $end_date = null;

    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = (int)($data['id'] ?? 0);
            $this->user_id = (int)($data['user_id'] ?? 0);
            $this->salary = (int)($data['salary'] ?? 0);
            $this->hire_date = $data['hire_date'] ?? null;
            $this->end_date = $data['end_date'] ?? null;
        }
    }

    public static function fromArray(array $data): self {
        return new self($data);
    }

    public static function addEntry(int $userId, int $salary = 0): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO " . self::$table . " (user_id, salary) VALUES (:user_id, :salary)");
        $stmt->execute([
            ':user_id' => $userId,
            ':salary' => $salary
        ]);
    }

    public static function endEntry(int $userId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE " . self::$table . " SET end_date = NOW() WHERE user_id = :user_id AND end_date IS NULL");
        $stmt->execute([':user_id' => $userId]);
    }

    public static function getActiveEmployees(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.role_id, u.full_name, r.name as role_name 
            FROM user u 
            JOIN angajat a ON u.id = a.user_id 
            JOIN role r ON u.role_id = r.id
            WHERE a.end_date IS NULL
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
