<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\Models\Users;

class UserService extends Injectable
{
    protected $users = [];

    public function getAll()
    {
        if (!$this->users) {
            $result = Users::find("active='Y'");
            foreach ($result as $user) {
                $id = $user->id;
                $this->users[$id] = $user->toArray();
            }
        }

        return $this->users;
    }

    // by user id
    public function get($id)
    {
        if (!$this->users) {
            $this->getAll();
        }
        return isset($this->users[$id]) ? $this->users[$id] : false;
    }

    // by user username
    public function find($name)
    {
        $user = Users::findFirst("username='$name'");

        // TODO: convert to entity ($this->toEntity(user))
        if ($user) {
            return $user->toArray();
        }

        return false;
    }

    public function activate($id)
    {
        $user = Users::findFirst($id);
        if ($user) {
            $user->active = 'Y';
            $user->save();
        }
    }

    public function deactivate($id)
    {
        $user = Users::findFirst($id);
        if ($user) {
            $user->active = 'N';
            $user->save();
        }
    }

    public function changePassword($id, $newPassword)
    {
        $user = Users::findFirst($id);
        if ($user) {
            $user->password = $this->security->hash($newPassword);
            $user->save();
        }
    }

    public function add($info)
    {
        $user = new Users();

        $user->username = $info['username'];
        $user->password = $info['password'];
        $user->email    = $info['email'];
        $user->role     = 0;
        $user->active   = isset($info['active']) ? $info['active'] : 'Y';

        $user->save();

        return $user->id;
    }

    public function getUserProjects($userId)
    {
        $sql = "SELECT projects FROM user_projects WHERE user_id=$userId";
        $row = $this->db->fetchOne($sql);

        if ($row) {
            $include = [];
            $exclude = [];

            $allProjects = array_keys($this->projectService->getAll());

            $projects = explode(',', $row['projects']);
            foreach ($projects as $projectId) {
                if ($projectId == '*') {
                    $include = $allProjects;
                }
                if ($projectId > 0) {
                    $include[] = $projectId;
                }
                if ($projectId < 0) {
                    $exclude[] = abs($projectId);
                }
            }
            return array_diff($include, $exclude);
        }

        return [];
    }
}
