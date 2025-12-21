<?php

require_once __DIR__ . '/Model.php';

class Message extends Model {
    private static string $table = 'mesaj';
    
    public int $id = 0;
    public string $sender_name = '';
    public string $sender_email = '';
    public string $subject = '';
    public string $body = '';
    public int $is_archived = 0;
    public ?string $sent_at = null;

    public function __construct(array $data = []) {
        if (!empty($data)) {
            $this->id = (int)($data['id'] ?? 0);
            $this->sender_name = $data['sender_name'] ?? '';
            $this->sender_email = $data['sender_email'] ?? '';
            $this->subject = $data['subject'] ?? '';
            $this->body = $data['body'] ?? '';
            $this->is_archived = (int)($data['is_archived'] ?? 0);
            $this->sent_at = $data['sent_at'] ?? null;
        }
    }

    public static function fromArray(array $data): self {
        return new self($data);
    }

    public static function createMessage(string $name, string $email, string $subject, string $body): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO " . self::$table . " (sender_name, sender_email, subject, body, is_archived) VALUES (:sender_name, :sender_email, :subject, :body, 0)");
        $stmt->execute([
            ':sender_name' => $name,
            ':sender_email' => $email,
            ':subject' => $subject,
            ':body' => $body
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function allMessages(): array {
        $model = new self();
        $rawMessages = $model->all(self::$table);
        $messages = [];
        foreach ($rawMessages as $messageData) {
            $messages[] = self::fromArray($messageData);
        }
        return $messages;
    }

    public static function archive(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE " . self::$table . " SET is_archived = 1 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function dearchive(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE " . self::$table . " SET is_archived = 0 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
