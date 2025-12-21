<?php

require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/JWT.php';

class Auth {

    const COOKIE_NAME = 'auth_token';
    const REFRESH_COOKIE = 'refresh_token';

    private static function secret(): string {
        return getenv('JWT_SECRET') ?: 'default_secret_key';
    }

    private static function issuer(): string {
        return getenv('JWT_ISSUER') ?: 'http://localhost';
    }

    private static function audience(): string {
        return getenv('JWT_AUDIENCE') ?: 'http://localhost';
    }

    private static function ttl(): int {
        return (int)(getenv('JWT_TTL') ?: 3600); // Default to 1 hour
    }

    private static function refreshTtl(): int {
        return (int)(getenv('JWT_REFRESH_TTL') ?: 604800); // Default to 1 week
    }

    public static function loginSetCookies(int $userId): void {
        $now = time();
        $payload = [
            'iss' => self::issuer(),
            'aud' => self::audience(),
            'sub' => $userId,
            'iat' => $now
        ];

        $token = JWT::encode($payload, self::secret(), self::ttl());
        setcookie(self::COOKIE_NAME, $token, [
            'expires' => $now + self::ttl(),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE_NAME] = $token;

        $refreshToken = bin2hex(random_bytes(32));
        $hash = hash('sha256', $refreshToken);
        $expires = (new DateTime())->add(new DateInterval('PT' . self::refreshTtl() . 'S'))->format('Y-m-d H:i:s');

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO refresh_tokens (user_id, token_hash, expires_at, user_agent, ip) VALUES (:user_id, :token_hash, :expires_at, :user_agent, :ip)");
        $stmt->execute([
            ':user_id' => $userId,
            ':token_hash' => $hash,
            ':expires_at' => $expires,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        setcookie(self::REFRESH_COOKIE, $refreshToken, [
            'expires' => $now + self::refreshTtl(),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::REFRESH_COOKIE] = $refreshToken;
    }

    public static function logout() : void {
        if (isset($_COOKIE[self::REFRESH_COOKIE])) {
            $hash = hash('sha256', $_COOKIE[self::REFRESH_COOKIE]);
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM refresh_tokens WHERE token_hash = :token_hash");
            $stmt->execute([':token_hash' => $hash]);
        }

        setcookie(self::COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => '/',
        ]);
        setcookie(self::REFRESH_COOKIE, '', [
            'expires' => time() - 3600,
            'path' => '/',
        ]);
        unset($_COOKIE[self::COOKIE_NAME], $_COOKIE[self::REFRESH_COOKIE]);
    }

    public static function refresh(): ?string {
        if (empty($_COOKIE[self::REFRESH_COOKIE]))
            return null;

        $refreshToken = $_COOKIE[self::REFRESH_COOKIE];
        $hash = hash('sha256', $refreshToken);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT user_id, expires_at FROM refresh_tokens WHERE token_hash = :token_hash");
        $stmt->execute([':token_hash' => $hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row)
            return null;

        $expiresAt = new DateTime($row['expires_at']);
        $now = new DateTime();
        if ($now > $expiresAt) {
            $stmt = $pdo->prepare("DELETE FROM refresh_tokens WHERE token_hash = :token_hash");
            $stmt->execute([':token_hash' => $hash]);
            setcookie(self::REFRESH_COOKIE, '', [
                'expires' => time() - 3600,
                'path' => '/',
            ]);
            unset($_COOKIE[self::REFRESH_COOKIE]);
            return null;
        }

        $userId = (int)$row['user_id'];
        $jwt = JWT::encode([
            'iss' => self::issuer(),
            'aud' => self::audience(),
            'sub' => $userId,
            'iat' => time()
        ], self::secret(), self::ttl());
        setcookie(self::COOKIE_NAME, $jwt, [
            'expires' => time() + self::ttl(),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE_NAME] = $jwt;
        return $jwt;
    }

    public static function user(): ?User {
        $token = null;
        $authHeader = self::getAuthorizationHeader();
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        } elseif (!empty($_COOKIE[self::COOKIE_NAME])) {
            $token = $_COOKIE[self::COOKIE_NAME];
        }
        if (!$token) {
            return null;
        }
        $decoded = JWT::decode($token, self::secret(), [self::issuer()], [self::audience()]);
        if (!$decoded) {
            return null;
        }

        if (!isset($decoded['sub'])) {
            return null;
        }

        if (!isset($decoded['iss']) || $decoded['iss'] !== self::issuer()) {
            return null;
        }
        if (!isset($decoded['aud']) || $decoded['aud'] !== self::audience()) {
            return null;
        }

        $userId = (int)$decoded['sub'];
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $userData ? User::fromArray($userData) : null;
    }

    public static function requireLogin(): void {
        if (!self::user()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Trebuie să fii autentificat pentru a accesa această pagină.'];
            header('Location: /login');
            exit;
        }
    }

    public static function requireAdmin(): void {
        $user = self::user();
        if (!$user || $user->role_id !== ADMIN_ROLE_ID) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Acces interzis. Această pagină este rezervată administratorilor.'];
            header('Location: /');
            exit;
        }
    }

    private static function getAuthorizationHeader(): ?string {
        if (isset($_SERVER['Authorization'])) {
            return trim($_SERVER["Authorization"]);
        }

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
            return trim($_SERVER["HTTP_AUTHORIZATION"]);
        }

        return null;
    }
}
