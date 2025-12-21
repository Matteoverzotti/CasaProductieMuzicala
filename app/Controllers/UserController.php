<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/../Constants/constants.php';

class UserController extends Controller {

    public function showCurrentUserProfile() : void {
        $currentUser = Auth::user();
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }

        $this->render('User/show', ['user' => $currentUser]);
    }

    public function showAllUsers() : void {
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->role_id !== ADMIN_ROLE_ID) {
            $_SESSION["flash"] = ["message" => "Acces interzis.", "type" => "error"];
            header('Location: /');
            exit;
        }
        $this->render('Users/show');
    }

    // TODO: Maybe refactor this and createAccount because they share a lot of code
    public function editProfile() : void {
        $currentUser = Auth::user();
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $current_password = $_POST['current_password'] ?? '';

            if (empty($current_password) || !$currentUser->verifyPassword($current_password)) {
                $_SESSION['flash'] = ['message' => 'Parola curentă este incorectă.', 'type' => 'error'];
            } elseif (empty($username) || empty($full_name) || empty($email)) {
                $_SESSION['flash'] = ['message' => 'Username-ul, numele complet și email-ul sunt obligatorii.', 'type' => 'error'];
            } elseif (!empty($password) && $password !== $confirm_password) {
                $_SESSION['flash'] = ['message' => 'Parolele noi nu se potrivesc.', 'type' => 'error'];
            } else {
                $existingUserByUsername = User::getByUsername($username);
                if ($existingUserByUsername && $existingUserByUsername->id !== $currentUser->id) {
                    $_SESSION['flash'] = ['message' => 'Numele de utilizator este deja folosit de alt cont.', 'type' => 'error'];
                } else {
                    $existingUserByEmail = User::getByEmail($email);
                    if ($existingUserByEmail && $existingUserByEmail->id !== $currentUser->id) {
                        $_SESSION['flash'] = ['message' => 'Email-ul este deja folosit de alt cont.', 'type' => 'error'];
                    } else {
                        $updatePassword = !empty($password) ? $password : null;
                        if (User::updateUser($currentUser->id, $username, $full_name, $email, $updatePassword)) {
                            $_SESSION['flash'] = ['message' => 'Profilul a fost actualizat cu succes!', 'type' => 'success'];
                            $currentUser = User::getUserById($currentUser->id);
                        } else {
                            $_SESSION['flash'] = ['message' => 'A apărut o eroare la actualizarea profilului.', 'type' => 'error'];
                        }
                    }
                }
            }
        }

        $this->render('User/edit', [
            'user' => $currentUser
        ]);
    }

    public function deleteAccount() : void {
        $currentUser = Auth::user();
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (User::deleteUser($currentUser->id)) {
                Auth::logout();
                $_SESSION['flash'] = ['message' => 'Contul a fost șters cu succes.', 'type' => 'success'];
                header('Location: /');
                exit;
            } else {
                $_SESSION['flash'] = ['message' => 'A apărut o eroare la ștergerea contului.', 'type' => 'error'];
            }
        }

        $this->render('User/delete', [
            'user' => $currentUser
        ]);
    }
}
