<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {

    private static function getMailer(): PHPMailer {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'] ?? '';
        $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
        $mail->CharSet = 'UTF-8';

        // Default sender
        $mail->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'contact@casadeproductie.com',
            $_ENV['MAIL_FROM_NAME'] ?? 'Casa de Producție'
        );

        return $mail;
    }

    public static function sendContactEmail(
        string $senderName,
        string $senderEmail,
        string $subject,
        string $body
    ): bool {
        try {
            $mail = self::getMailer();

            $adminEmail = $_ENV['ADMIN_EMAIL'] ?? $_ENV['MAIL_USERNAME'];
            $mail->addAddress($adminEmail);
            $mail->addReplyTo($senderEmail, $senderName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "[Contact Form] " . $subject;

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px;'>
                    <h2 style='color: #333;'>Mesaj nou din formularul de contact</h2>
                    <hr style='border: 1px solid #ddd;'>
                    <p><strong>Nume:</strong> " . htmlspecialchars($senderName) . "</p>
                    <p><strong>Email:</strong> <a href='mailto:" . htmlspecialchars($senderEmail) . "'>" . htmlspecialchars($senderEmail) . "</a></p>
                    <hr style='border: 1px solid #ddd;'>
                    <p><strong>Subiect:</strong> " . htmlspecialchars($subject) . "</p>
                    <div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 10px;'>
                        <p style='white-space: pre-wrap;'>" . htmlspecialchars($body) . "</p>
                    </div>
                    <hr style='border: 1px solid #ddd;'>
                    <small style='color: #888;'>Acest mesaj a fost trimis de pe site-ul Casa de Producție</small>
                </div>
            ";

            $mail->AltBody = "Mesaj de la: {$senderName} ({$senderEmail})\n" .
                            "Subiect: {$subject}\n\n{$body}";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Mail sending failed: " . $e->getMessage());
            return false;
        }
    }
}
