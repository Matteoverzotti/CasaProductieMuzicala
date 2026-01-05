<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../Database.php';

class Album extends Model {
    private static string $table = 'album';
    
    public int $id = 0;
    public string $title = '';
    public int $artist_id = 0;
    public ?int $release_year = null;
    public ?string $genre = null;
    public ?string $artist_name = null;
    
    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = (int)($data['id'] ?? 0);
            $this->title = $data['title'] ?? '';
            $this->artist_id = (int)($data['artist_id'] ?? 0);
            $this->release_year = isset($data['release_year']) ? (int)$data['release_year'] : null;
            $this->genre = $data['genre'] ?? null;
            $this->artist_name = $data['artist_name'] ?? null;
        }
    }
    
    public static function fromArray(array $data): self {
        return new self($data);
    }
    
    public static function getAllAlbums(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, ar.stage_name as artist_name, ar.genre as artist_genre
            FROM " . self::$table . " a
            JOIN artist ar ON a.artist_id = ar.id
            ORDER BY a.release_year DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getAlbumById(int $id): ?Album {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, ar.stage_name as artist_name, ar.genre as artist_genre
            FROM " . self::$table . " a
            JOIN artist ar ON a.artist_id = ar.id
            WHERE a.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? self::fromArray($result) : null;
    }

    public static function getAlbumByTitleAndArtist(string $title, int $artistId): ?Album {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT *
            FROM " . self::$table . "
            WHERE title = :title AND artist_id = :artist_id
        ");
        $stmt->execute([':title' => $title, ':artist_id' => $artistId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? self::fromArray($result) : null;
    }

    public static function createAlbum(array $data): Album {
        $pdo = Database::getConnection();
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":$f", $fields);
        
        $sql = "INSERT INTO " . self::$table . " (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        $data['id'] = (int)$pdo->lastInsertId();
        return self::fromArray($data);
    }
}
