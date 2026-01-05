<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../Database.php';

class Track extends Model {
    private static string $table = 'piesa';
    
    public int $id = 0;
    public int $proiect_id = 0;
    public int $album_id = 0;
    public string $title = '';
    public int $duration = 0;
    public ?int $release_year = null;
    public string $status = 'draft';
    
    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = (int)($data['id'] ?? 0);
            $this->proiect_id = (int)($data['proiect_id'] ?? 0);
            $this->album_id = (int)($data['album_id'] ?? 0);
            $this->title = $data['title'] ?? '';
            $this->duration = (int)($data['duration'] ?? 0);
            $this->release_year = isset($data['release_year']) ? (int)$data['release_year'] : null;
            $this->status = $data['status'] ?? 'draft';
        }
    }
    
    public static function fromArray(array $data): self {
        return new self($data);
    }
    
    public static function create(int $proiectId, int $albumId, string $title, int $duration, int $release_year): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO " . self::$table . " (proiect_id, album_id, title, duration, release_year, status) VALUES 
        (:proiect_id, :album_id, :title, :duration, :release_year, 'released')");

        $stmt->execute([
            ':proiect_id' => $proiectId,
            ':album_id' => $albumId,
            ':title' => $title,
            ':duration' => $duration,
            ':release_year' => $release_year,
        ]);

        return (int)$pdo->lastInsertId();
    }
    
    public static function getTracksByAlbum(int $albumId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM " . self::$table . "
            WHERE album_id = :album_id
            ORDER BY id ASC
        ");
        $stmt->execute([':album_id' => $albumId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getAllReleasedTracks(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, a.title as album_title, ar.stage_name as artist_name
            FROM " . self::$table . " p
            JOIN album a ON p.album_id = a.id
            JOIN artist ar ON a.artist_id = ar.id
            WHERE p.status = 'released'
            ORDER BY p.release_year DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
