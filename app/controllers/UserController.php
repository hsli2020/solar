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

        $auth = $this->session->get('auth');
        if (is_array($auth)) {
            return $this->response->redirect("/");
        }

        $username = '';

        if ($this->request->isPost() && $this->security->checkToken()) {
            // Receiving the variables sent by POST
            $username = $this->request->getPost('username', 'trim');
            $password = $this->request->getPost('password', 'trim');

            // find user in the database
            $user = Users::findFirstByUsername($username);

            if ($user && $user->active == 'Y' && $this->security->checkHash($password, $user->password)) {
                $this->_registerSession($user);
                return $this->response->redirect("/");
            } else {
                // To protect against timing attacks. Regardless of whether a user exists or not,
                // the script will take roughly the same amount as it will always be computing a hash.
                $this->security->hash(rand());
            }

            //$this->getFlashSession('error', 'Wrong email/password.', false);
        }

        $this->view->username = $username;
    }

    public function logoutAction()
    {
        $this->session->destroy();
        return $this->response->redirect("/user/login");
    }

    public function addAction()
    {
        $this->view->pageTitle = 'User Login';

        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return $this->response->redirect("/user/login");
        }

        if ($auth['role'] != 1) {
            return $this->response->redirect("/");
        }

        if ($this->request->isPost() && $this->security->checkToken()) {
            $username = $this->request->getPost('username', 'trim');
            $password = $this->request->getPost('password', 'trim');
            $email    = $this->request->getPost('email',    'trim');

            try {
                $user = new Users();
                $user->username = $username;
                $user->email    = $email;
                $user->role     = 0; // TODO ??
                $user->active   = 'Y';
                $user->password = $this->security->hash($password);
                $user->save();
            } catch (\Exception $e) {
                //fpr($e->getMessage());
                return;
            }

            //return $this->response->redirect("/");
        }
    }

    public function changePasswordAction()
    {
        echo __METHOD__;
    }

    public function resetPasswordAction()
    {
        echo __METHOD__;
    }

    public function forgotPasswordAction()
    {
        echo __METHOD__;
    }

    public function seedAction()
    {
        $auth = $this->session->get('auth');
        if (!is_array($auth)) {
            return $this->response->redirect("/user/login");
        }

        if ($auth['role'] != 1) {
            return $this->response->redirect("/");
        }

        // the passwords got from http://passwordsgenerator.net/
        $passwords = [
            1 => 'gcshs12345',
            2 => 'gcsws12345',
            3 => 'bp8V4FSJdU',
            4 => 'aegb4Sy2Ad',
        ];

        foreach ($passwords as $id => $password) {
            $user = Users::findFirst($id);
            $user->password = $this->security->hash($password);
            $user->save();
        }

        echo "<h2>users seeding is done.</h2>";
    }
}
