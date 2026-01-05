<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/ProjectFile.php';
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
            'isAuthor' => ($project->created_by === $user->id),
            'files' => ProjectFile::getFilesByProject($id, isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0),
            'parentId' => isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null,
            'currentFolder' => isset($_GET['parent_id']) ? ProjectFile::getFileById((int)$_GET['parent_id']) : null
        ]);
    }

    public function uploadFile(): void {
        $user = Auth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : 0;

        if (!$this->isProjectMember($projectId, $user->id)) {
            $_SESSION['flash'] = ['message' => 'Unauthorized', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['message' => 'File upload error', 'type' => 'error'];
            header("Location: /project/show?id=$projectId" . ($parentId ? "&parent_id=$parentId" : ""));
            exit;
        }

        $file = $_FILES['file'];
        $originalName = $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Security: Whitelist extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'mp3', 'wav', 'zip'];
        if (!in_array($extension, $allowedExtensions)) {
            $_SESSION['flash'] = ['message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions), 'type' => 'error'];
            header("Location: /project/show?id=$projectId" . ($parentId ? "&parent_id=$parentId" : ""));
            exit;
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetDir = __DIR__ . '/../../storage/projects/' . $projectId;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            ProjectFile::create([
                'project_id' => $projectId,
                'user_id' => $user->id,
                'parent_id' => $parentId,
                'is_directory' => false,
                'filename' => $originalName,
                'original_name' => $originalName,
                'file_path' => $projectId . '/' . $filename,
                'file_size' => $file['size'],
                'mime_type' => $file['type']
            ]);

            $_SESSION['flash'] = ['message' => 'File uploaded successfully', 'type' => 'success'];
        } else {
            $_SESSION['flash'] = ['message' => 'Failed to move uploaded file', 'type' => 'error'];
        }

        header("Location: /project/show?id=$projectId" . ($parentId ? "&parent_id=$parentId" : ""));
        exit;
    }

    public function createFolder(): void {
        $user = Auth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : 0;
        $folderName = $_POST['folder_name'] ?? '';

        if (empty($folderName)) {
            $_SESSION['flash'] = ['message' => 'Folder name is required', 'type' => 'error'];
            header("Location: /project/show?id=$projectId" . ($parentId ? "&parent_id=$parentId" : ""));
            exit;
        }

        if (!$this->isProjectMember($projectId, $user->id)) {
            $_SESSION['flash'] = ['message' => 'Unauthorized', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        ProjectFile::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'is_directory' => true,
            'filename' => $folderName
        ]);

        $_SESSION['flash'] = ['message' => 'Folder created successfully', 'type' => 'success'];
        header("Location: /project/show?id=$projectId" . ($parentId ? "&parent_id=$parentId" : ""));
        exit;
    }

    public function deleteFile(): void {
        $user = Auth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $fileId = (int)($_POST['file_id'] ?? 0);
        $file = ProjectFile::getFileById($fileId);

        if (!$file) {
            $_SESSION['flash'] = ['message' => 'File not found', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        if (!$this->isProjectMember($file->project_id, $user->id)) {
            $_SESSION['flash'] = ['message' => 'Unauthorized', 'type' => 'error'];
            header('Location: /');
            exit;
        }

        $this->recursivePhysicalDelete($fileId);
        ProjectFile::delete($fileId);

        $_SESSION['flash'] = ['message' => 'Item deleted successfully', 'type' => 'success'];
        header("Location: /project/show?id=" . $file->project_id . ($file->parent_id ? "&parent_id=" . $file->parent_id : ""));
        exit;
    }

    private function recursivePhysicalDelete(int $fileId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, is_directory, file_path FROM project_file WHERE parent_id = :parent_id");
        $stmt->execute([':parent_id' => $fileId]);
        $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($children as $child) {
            if ($child['is_directory']) {
                $this->recursivePhysicalDelete((int)$child['id']);
            } else if ($child['file_path']) {
                $fullPath = __DIR__ . '/../../storage/projects/' . $child['file_path'];
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        }

        // Also delete the item itself
        $item = ProjectFile::getFileById($fileId);
        if ($item && !$item->is_directory && $item->file_path) {
            $fullPath = __DIR__ . '/../../storage/projects/' . $item->file_path;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    public function downloadFile(): void {
        $user = Auth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        $fileId = (int)($_GET['file_id'] ?? 0);
        $file = ProjectFile::getFileById($fileId);

        if (!$file || $file->is_directory) {
            http_response_code(404);
            echo "File not found";
            return;
        }

        if (!$this->isProjectMember($file->project_id, $user->id)) {
            http_response_code(403);
            echo "Unauthorized";
            return;
        }

        $fullPath = __DIR__ . '/../../storage/projects/' . $file->file_path;
        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo "File not found";
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($file->mime_type ?: 'application/octet-stream'));
        $safeFilename = str_replace('"', '', basename($file->original_name));
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    private function isProjectMember(int $projectId, int $userId): bool {
        $projectUsers = Project::getProjectUsers($projectId);
        $isMember = false;
        foreach ($projectUsers as $pu) {
            if ($pu['id'] === $userId && $pu['status'] === 'approved') {
                $isMember = true;
                break;
            }
        }

        $user = Auth::user();
        if ($user && $user->role_id === ADMIN_ROLE_ID) return true;

        return $isMember;
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
