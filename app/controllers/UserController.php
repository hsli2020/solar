<?php

namespace App\Controllers;

use App\Models\Users;

class UserController extends ControllerBase
{
    public function indexAction()
    {
    }

    private function _registerSession($user)
    {
        $this->session->set('auth', array(
            'id'       => $user->id,
            'username' => $user->username,
            'role'     => $user->role
        ));
    }

    public function loginAction()
    {
        $this->view->pageTitle = 'User Login';

        if ($this->request->isPost() ) {

            // Receiving the variables sent by POST
            $username = $this->filter->sanitize($this->request->getPost('username'), "trim");
            $password = $this->filter->sanitize($this->request->getPost('password'), "trim");

            // find user in the database
            $user = Users::findFirst(array(
                "username = :username: AND password = :password: AND active = 'Y'",
                "bind" => array(
                    'username' => $username,
                    'password' => md5($password),
                )
            ));

            if (!empty($user)) {
                $this->_registerSession($user);
                return $this->response->redirect("/");
            }

            //$this->getFlashSession('error', 'Wrong email/password.', false);
        }
    }

    public function logoutAction()
    {
        $this->session->destroy();
        return $this->response->redirect("/user/login");
    }
}
