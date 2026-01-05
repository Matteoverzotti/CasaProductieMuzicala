<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Message.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Services/MailService.php';
require_once __DIR__ . '/../../middleware/Auth.php';

class ContactController extends Controller {

    public function contact(): void {
        $user = Auth::user();

        // Admins cannot send messages to themselves
        if ($user && $user->role_id === ADMIN_ROLE_ID) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Administratorii nu își pot trimite mesaje singuri.'];
            header('Location: /');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data - use user data if authenticated
            $name = $user ? ($user->full_name ?: $user->username) : trim($_POST['name'] ?? '');
            $email = $user ? $user->email : trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');
            
            // Validation
            if (empty($name) || empty($email) || empty($subject) || empty($body)) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Numele, email-ul, subiectul și mesajul sunt obligatorii.'];
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Adresa de email nu este validă.'];
            } elseif (!$this->validateRecaptcha()) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Validarea reCAPTCHA a eșuat. Vă rugăm să încercați din nou.'];
            } else {
                // Save message to database
                Message::createMessage($name, $email, $subject, $body);
                
                // Send email notification to admin
                $emailSent = MailService::sendContactEmail($name, $email, $subject, $body);
                
                if ($emailSent) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Mesajul a fost trimis cu succes!'];
                } else {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Mesajul a fost salvat. Echipa noastră îl va primi în curând.'];
                }
                
                header('Location: /');
                exit;
            }
        }
        
        $this->render('Contact/form', [
            'user' => $user
        ]);
    }

    public function adminMessages(): void {
        Auth::requireAdmin();
        
        $messages = Message::allMessages();
        $user = Auth::user();
        
        $this->render('Contact/admin_messages', [
            'messages' => $messages,
            'user' => $user
        ]);
    }

    public function archiveMessage(): void {
        Auth::requireAdmin();
        
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Message::archive($id);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Mesaj arhivat.'];
        }
        
        header('Location: /admin/messages');
        exit;
    }

    public function dearchiveMessage(): void {
        Auth::requireAdmin();
        
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Message::dearchive($id);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Mesaj dezarhivat.'];
        }
        
        header('Location: /admin/messages');
        exit;
    }
}
