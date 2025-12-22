<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../Database.php';

class Project extends Model {
    private static string $table = 'proiect';

    public int $id = 0;
    public string $title = '';
    public int $created_by = 0;
    public ?string $start_date = null;
    public ?string $end_date = null;

    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = (int)($data['id'] ?? 0);
            $this->title = $data['title'] ?? '';
            $this->created_by = (int)($data['created_by'] ?? 0);
            $this->start_date = $data['start_date'] ?? null;
            $this->end_date = $data['end_date'] ?? null;
        }
    }

    public static function fromArray(array $data): self {
        return new self($data);
    }

    public static function create(string $title, int $created_by): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO " . self::$table . " (title, created_by) VALUES (:title, :created_by)");
        $stmt->execute([
            ':title' => $title,
            ':created_by' => $created_by
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function assignUser(int $project_id, int $user_id, string $status = 'pending'): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO proiect_user (proiect_id, user_id, status) VALUES (:project_id, :user_id, :status)");
        return $stmt->execute([
            ':project_id' => $project_id,
            ':user_id' => $user_id,
            ':status' => $status
        ]);
    }

    public static function updateUserStatus(int $project_id, int $user_id, string $status): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE proiect_user SET status = :status WHERE proiect_id = :project_id AND user_id = :user_id");
        return $stmt->execute([
            ':project_id' => $project_id,
            ':user_id' => $user_id,
            ':status' => $status
        ]);
    }

    public static function getProjectsByUser(int $user_id): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.* FROM " . self::$table . " p
            JOIN proiect_user pu ON p.id = pu.proiect_id
            WHERE pu.user_id = :user_id AND pu.status = 'approved'
        ");
        $stmt->execute([':user_id' => $user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $projects = [];
        foreach ($rows as $row) {
            $projects[] = self::fromArray($row);
        }
        return $projects;
    }

    public static function getPendingProjectsByUser(int $user_id): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.* FROM " . self::$table . " p
            JOIN proiect_user pu ON p.id = pu.proiect_id
            WHERE pu.user_id = :user_id AND pu.status = 'pending'
        ");
        $stmt->execute([':user_id' => $user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $projects = [];
        foreach ($rows as $row) {
            $projects[] = self::fromArray($row);
        }
        return $projects;
    }

    public static function getProjectById(int $id): ?Project {
        $model = new self();
        $projectData = $model->getById(self::$table, $id);
        return $projectData ? self::fromArray($projectData) : null;
    }

    public static function getProjectUsers(int $project_id): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.full_name, pu.status 
            FROM user u
            JOIN proiect_user pu ON u.id = pu.user_id
            WHERE pu.proiect_id = :project_id
        ");
        $stmt->execute([':project_id' => $project_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM " . self::$table . " WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function reRequestApproval(int $project_id, int $user_id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE proiect_user SET status = 'pending' WHERE proiect_id = :project_id AND user_id = :user_id");
        return $stmt->execute([
            ':project_id' => $project_id,
            ':user_id' => $user_id
        ]);
    }
}
