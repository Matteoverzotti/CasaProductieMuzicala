<?php

require_once __DIR__ . '/Model.php';

class User extends Model {
    private static string $table = 'user';
    
    public int $id = 0;
    public int $role_id = 1;
    public string $username = '';
    public string $email = '';
    public string $full_name = '';
    public string $password_hash = '';
    public ?string $created_at = null;
    public ?string $updated_at = null;

    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = (int)($data['id'] ?? 0);
            $this->role_id = (int)($data['role_id'] ?? 1);
            $this->username = $data['username'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->full_name = $data['full_name'] ?? '';
            $this->password_hash = $data['password_hash'] ?? '';
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }

    public static function fromArray(array $data): self {
        return new self($data);
    }

    /**
     * Retrieve a user by their ID.
     *
     * @param int $id The ID of the user to retrieve.
     * @return User|null The user instance, or null if not found.
     */
    public static function getUserById(int $id) : ?User {
        $model = new self();
        $userData = $model->getById(self::$table, $id);
        return $userData ? self::fromArray($userData) : null;
    }

    /**
     * Create a new user record.
     *
     * @param string $username The username of the new user.
     * @param string $email The email of the new user.
     * @param string $password The plaintext password of the new user.
     * @return int The ID of the newly created user.
     */
    public static function createUser(string $username, string $full_name, string $email, string $password, int $role = 1): int {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO " . self::$table . " (role_id, username, email, password_hash, full_name) VALUES (:role_id, :username, :email, :password_hash, :full_name)");
        $stmt->execute([
            ':role_id' => $role,
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $hash,
            ':full_name' => $full_name
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Retrieve a user by their username.
     *
     * @param string $username The username of the user to retrieve.
     * @return User|null The user instance, or null if not found.
     */
    public static function getByUsername(string $username) : ?User {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM " . self::$table . " WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? self::fromArray($user) : null;
    }

    /**
     * Retrieve a user by their email.
     *
     * @param string $email The email of the user to retrieve.
     * @return User|null The user instance, or null if not found.
     */
    public static function getByEmail(string $email) : ?User {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM " . self::$table . " WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? self::fromArray($user) : null;
    }
}
