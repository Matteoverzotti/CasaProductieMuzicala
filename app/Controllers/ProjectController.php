<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../middleware/Auth.php';

class ProjectController extends Controller {

    public function create() : void {
        $user = Auth::user();
        if (!$user || !in_array($user->role_id, [ARTIST_ROLE_ID, SOUND_ENGINEER_ROLE_ID, PRODUCER_ROLE_ID])) {
            $_SESSION['flash'] = ['message' => 'Unauthorized', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        $title = $_POST['title'] ?? '';
        $assigned_users = $_POST['assigned_users'] ?? [];

        if (empty($title)) {
            $_SESSION['flash'] = ['message' => 'Title is required', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        $projectId = Project::create($title, $user->id);
        
        // The creator is automatically approved
        Project::assignUser($projectId, $user->id, 'approved');

        foreach ($assigned_users as $assigned_user_id) {
            if ($assigned_user_id != $user->id) {
                Project::assignUser($projectId, (int)$assigned_user_id, 'pending');
            }
        }

        $_SESSION['flash'] = ['message' => 'Project created successfully', 'type' => 'success'];
        header('Location: /');
        exit;
    }

    public function updateStatus() : void {
        $user = Auth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!$projectId || !in_array($status, ['approved', 'denied'])) {
            $_SESSION['flash'] = ['message' => 'Invalid request', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        Project::updateUserStatus($projectId, $user->id, $status);

        $_SESSION['flash'] = ['message' => 'Project status updated', 'type' => 'success'];
        header('Location: /');
        exit;
    }

    public function show() : void {
        $user = Auth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $project = Project::getProjectById($id);

        if (!$project) {
            http_response_code(404);
            echo "Project not found";
            return;
        }

        $projectUsers = Project::getProjectUsers($id);
        
        // Check if current user is part of the project or is admin
        $isMember = false;
        foreach ($projectUsers as $pu) {
            if ($pu['id'] === $user->id) {
                $isMember = true;
                break;
            }
        }

        if (!$isMember && $user->role_id !== ADMIN_ROLE_ID) {
            $_SESSION['flash'] = ['message' => 'Access denied', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        $this->render('Project/show', [
            'user' => $user,
            'project' => $project,
            'projectUsers' => $projectUsers,
            'isAuthor' => ($project->created_by === $user->id)
        ]);
    }

    public function delete() : void {
        $user = Auth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $id = (int)($_POST['project_id'] ?? 0);
        $project = Project::getProjectById($id);

        if (!$project) {
            $_SESSION['flash'] = ['message' => 'Project not found', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        if ($project->created_by !== $user->id && $user->role_id !== ADMIN_ROLE_ID) {
            $_SESSION['flash'] = ['message' => 'Only the author can delete the project', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        Project::delete($id);

        $_SESSION['flash'] = ['message' => 'Project deleted successfully', 'type' => 'success'];
        header('Location: /');
        exit;
    }

    public function reRequest() {
        $user = Auth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $targetUserId = (int)($_POST['user_id'] ?? 0);

        $project = Project::getProjectById($projectId);
        if (!$project || ($project->created_by !== $user->id && $user->role_id !== ADMIN_ROLE_ID)) {
            $_SESSION['flash'] = ['message' => 'Unauthorized', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        Project::reRequestApproval($projectId, $targetUserId);

        $_SESSION['flash'] = ['message' => 'Approval re-requested', 'type' => 'success'];
        header("Location: /project/show?id=$projectId");
        exit;
    }
}
