<?php

require_once __DIR__ . '/Model.php';

class Artist extends Model {
    private static string $table = 'artist';
    
    public int $id = 0;
    public string $stage_name = '';
    public ?string $bio = null;
    public ?string $country = null;
    public ?string $genre = null;

    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = (int)($data['id'] ?? 0);
            $this->stage_name = $data['stage_name'] ?? '';
            $this->bio = $data['bio'] ?? null;
            $this->country = $data['country'] ?? null;
            $this->genre = $data['genre'] ?? null;
        }
    }

    public static function allArtists(): array {
        $model = new self();
        $rawArtists = $model->all(self::$table);
        $users = [];
        foreach ($rawArtists as $artistData) {
            $users[] = self::fromArray($artistData);
        }
        return $users;
    }

    public static function fromArray(array $data): self {
        return new self($data);
    }

    public static function getArtistById(int $id) : ?Artist {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM " . self::$table . " WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $artistData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $artistData ? self::fromArray($artistData) : null;
    }

    public static function getArtistByStageName(string $stageName) : ?Artist {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM " . self::$table . " WHERE stage_name = :stage_name");
        $stmt->execute([':stage_name' => $stageName]);
        $artistData = $stmt->fetch(PDO::FETCH_ASSOC);
        return $artistData ? self::fromArray($artistData) : null;
    }

    public static function createArtist(int $id, string $stage_name, ?string $bio, ?string $country, ?string $genre): int {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO " . self::$table . " (id, stage_name, bio, country, genre) 
                VALUES (:id, :stage_name, :bio, :country, :genre)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':stage_name' => $stage_name,
            ':bio' => $bio,
            ':country' => $country,
            ':genre' => $genre
        ]);
        return $id;
    }
}
