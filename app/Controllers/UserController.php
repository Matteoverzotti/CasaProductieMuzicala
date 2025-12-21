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

        $this->render('User/show', ['user' => $currentUser, 'isOwnProfile' => true]);
    }

    public function showUserProfile() : void {
        $currentUser = Auth::user();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : ($currentUser ? $currentUser->id : 0);

        if (!$currentUser || ($currentUser->role_id !== ADMIN_ROLE_ID && $currentUser->id !== $id)) {
            $_SESSION["flash"] = ["message" => "Acces interzis.", "type" => "error"];
            header('Location: /');
            exit;
        }

        $user = User::getUserById($id);
        if (!$user) {
            http_response_code(404);
            echo "Utilizatorul nu a fost găsit.";
            return;
        }

        $this->render('User/show', [
            'user' => $user,
            'isOwnProfile' => ($currentUser->id === $user->id)
        ]);
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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : ($currentUser ? $currentUser->id : 0);

        if (!$currentUser || ($currentUser->role_id !== ADMIN_ROLE_ID && $currentUser->id !== $id)) {
            $_SESSION["flash"] = ["message" => "Acces interzis.", "type" => "error"];
            header('Location: /');
            exit;
        }

        $userToEdit = ($currentUser->id === $id) ? $currentUser : User::getUserById($id);

        if (!$userToEdit) {
            http_response_code(404);
            echo "Utilizatorul nu a fost găsit.";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $current_password = $_POST['current_password'] ?? '';
            $role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : null;

            $error = false;
            // Admins don't need current password to edit others
            if ($currentUser->id === $userToEdit->id && (empty($current_password) || !$currentUser->verifyPassword($current_password))) {
                $_SESSION['flash'] = ['message' => 'Parola curentă este incorectă.', 'type' => 'error'];
                $error = true;
            } elseif (empty($username) || empty($full_name) || empty($email)) {
                $_SESSION['flash'] = ['message' => 'Username-ul, numele complet și email-ul sunt obligatorii.', 'type' => 'error'];
                $error = true;
            } elseif (!empty($password) && $password !== $confirm_password) {
                $_SESSION['flash'] = ['message' => 'Parolele noi nu se potrivesc.', 'type' => 'error'];
                $error = true;
            }

            if (!$error) {
                $existingUserByUsername = User::getByUsername($username);
                if ($existingUserByUsername && $existingUserByUsername->id !== $userToEdit->id) {
                    $_SESSION['flash'] = ['message' => 'Numele de utilizator este deja folosit de alt cont.', 'type' => 'error'];
                } else {
                    $existingUserByEmail = User::getByEmail($email);
                    if ($existingUserByEmail && $existingUserByEmail->id !== $userToEdit->id) {
                        $_SESSION['flash'] = ['message' => 'Email-ul este deja folosit de alt cont.', 'type' => 'error'];
                    } else {
                        $updatePassword = !empty($password) ? $password : null;
                        // Only admins can change roles
                        $updateRoleId = ($currentUser->role_id === ADMIN_ROLE_ID) ? $role_id : null;

                        if (User::updateUser($userToEdit->id, $username, $full_name, $email, $updatePassword, $updateRoleId)) {
                            $_SESSION['flash'] = ['message' => 'Profilul a fost actualizat cu succes!', 'type' => 'success'];
                            $userToEdit = User::getUserById($userToEdit->id);
                            if ($currentUser->id === $userToEdit->id) {
                                $currentUser = $userToEdit;
                            }
                        } else {
                            $_SESSION['flash'] = ['message' => 'A apărut o eroare la actualizarea profilului.', 'type' => 'error'];
                        }
                    }
                }
            }
        }

        $this->render('User/edit', [
            'user' => $userToEdit,
            'isAdmin' => ($currentUser->role_id === ADMIN_ROLE_ID),
            'isOwnProfile' => ($currentUser->id === $userToEdit->id)
        ]);
    }

    public function deleteAccount() : void {
        $currentUser = Auth::user();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : ($currentUser ? $currentUser->id : 0);

        if (!$currentUser || ($currentUser->role_id !== ADMIN_ROLE_ID && $currentUser->id !== $id)) {
            $_SESSION["flash"] = ["message" => "Acces interzis.", "type" => "error"];
            header('Location: /');
            exit;
        }

        $userToDelete = ($currentUser->id === $id) ? $currentUser : User::getUserById($id);

        if (!$userToDelete) {
            http_response_code(404);
            echo "Utilizatorul nu a fost găsit.";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (User::deleteUser($userToDelete->id)) {
                if ($currentUser->id === $userToDelete->id) {
                    Auth::logout();
                    $_SESSION['flash'] = ['message' => 'Contul a fost șters cu succes.', 'type' => 'success'];
                    header('Location: /');
                } else {
                    $_SESSION['flash'] = ['message' => "Contul utilizatorului {$userToDelete->username} a fost șters.", 'type' => 'success'];
                    header('Location: /users');
                }
                exit;
            } else {
                $_SESSION['flash'] = ['message' => 'A apărut o eroare la ștergerea contului.', 'type' => 'error'];
            }
        }

        $this->render('User/delete', [
            'user' => $userToDelete,
            'isOwnAccount' => ($currentUser->id === $userToDelete->id)
        ]);
    }
}
