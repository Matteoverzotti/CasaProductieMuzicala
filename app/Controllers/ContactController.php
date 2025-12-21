<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Message.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../middleware/Auth.php';

class ContactController extends Controller {

    public function contact(): void {
        Auth::requireLogin();
        
        $user = Auth::user();

        if ($user->role_id === ADMIN_ROLE_ID) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Administratorii nu își pot trimite mesaje singuri.'];
            header('Location: /');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = $_POST['subject'] ?? '';
            $body = $_POST['body'] ?? '';
            
            if (empty($subject) || empty($body)) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Toate câmpurile sunt obligatorii.'];
            } else {
                $senderName = $user->full_name ?: $user->username;
                Message::createMessage($senderName, $user->email, $subject, $body);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Mesajul a fost trimis cu succes!'];
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
