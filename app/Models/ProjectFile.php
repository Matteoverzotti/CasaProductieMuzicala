<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../Database.php';

class ProjectFile extends Model {
    private static string $table = 'project_file';

    public int $id = 0;
    public int $project_id = 0;
    public int $user_id = 0;
    public int $parent_id = 0;
    public bool $is_directory = false;
    public string $filename = '';
    public ?string $original_name = null;
    public ?string $file_path = null;
    public ?int $file_size = null;
    public ?string $mime_type = null;
    public ?string $uploaded_at = null;

    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = (int)($data['id'] ?? 0);
            $this->project_id = (int)($data['project_id'] ?? 0);
            $this->user_id = (int)($data['user_id'] ?? 0);
            $this->parent_id = (int)($data['parent_id'] ?? 0);
            $this->is_directory = (bool)($data['is_directory'] ?? false);
            $this->filename = $data['filename'] ?? '';
            $this->original_name = $data['original_name'] ?? null;
            $this->file_path = $data['file_path'] ?? null;
            $this->file_size = isset($data['file_size']) ? (int)$data['file_size'] : null;
            $this->mime_type = $data['mime_type'] ?? null;
            $this->uploaded_at = $data['uploaded_at'] ?? null;
        }
    }

    public static function fromArray(array $data): self {
        return new self($data);
    }

    public static function create(array $data): int {
        $pdo = Database::getConnection();

        // Ensure boolean values are converted to integers for PDO compatibility with MySQL strict mode
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $data[$key] = (int)$value;
            }
        }

        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":$f", $fields);
        
        $sql = "INSERT INTO " . self::$table . " (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        return (int)$pdo->lastInsertId();
    }

    public static function getFilesByProject(int $project_id, int $parent_id = 0): array {
        $pdo = Database::getConnection();
        $sql = "SELECT pf.*, u.full_name as uploader_name 
                FROM " . self::$table . " pf
                JOIN user u ON pf.user_id = u.id
                WHERE pf.project_id = :project_id AND pf.parent_id = :parent_id
                ORDER BY pf.is_directory DESC, pf.filename ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':project_id' => $project_id, ':parent_id' => $parent_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getFileById(int $id): ?ProjectFile {
        $model = new self();
        $fileData = $model->getById(self::$table, $id);
        return $fileData ? self::fromArray($fileData) : null;
    }

    public static function delete(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM " . self::$table . " WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
