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
        $user = Users::findFirst($id);

        // TODO: convert to entity ($this->toEntity(user))
        if ($user) {
            return $user->toArray();
        }

        return false;
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
            $user->password = $newPassword;
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

    public function getSpecificProjects($userId)
    {
        $sql = "SELECT projects FROM user_projects WHERE user_id=$userId";

        $result = $this->db->fetchOne($sql);
        if ($result) {
            return explode(',', $result['projects']);
        }

        return [];
    }
}
