<?php

require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Project.php';
require_once __DIR__ . '/../Models/User.php';

class HomeController extends Controller {

    public function index() : void {
        $user = Auth::user();
        $projects = [];
        $pendingProjects = [];
        $assignableUsers = [];

        if ($user) {
            $projects = Project::getProjectsByUser($user->id);
            $pendingProjects = Project::getPendingProjectsByUser($user->id);
            
            if (in_array($user->role_id, [ARTIST_ROLE_ID, SOUND_ENGINEER_ROLE_ID, PRODUCER_ROLE_ID])) {
                $allUsers = User::allUsers();
                $assignableUsers = array_filter($allUsers, function($u) {
                    return in_array($u->role_id, [ARTIST_ROLE_ID, SOUND_ENGINEER_ROLE_ID, PRODUCER_ROLE_ID]);
                });
            }
        }

        $this->render('home', [
            'user' => $user,
            'projects' => $projects,
            'pendingProjects' => $pendingProjects,
            'assignableUsers' => $assignableUsers
        ]);
    }
}
