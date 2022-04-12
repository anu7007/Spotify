<?php

use Phalcon\Mvc\Controller;
use Phalcon\Session\Manager;
use Phalcon\Http\Response\Cookies;

class LoginController extends Controller
{
    public function indexAction()
    {
        $users = new Users();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $this->view->users = Users::find();
        if ($this->request->isPost('login')) {
            if (empty($email) || empty($password)) {
                $this->session->set("msg", "*Please fill all fields");
            } else {
                $user = Users::findFirst(array(
                    'email = :email: and password = :password:', 'bind' => array(
                        'email' => $this->request->getPost("email"),
                        'password' => $this->request->getPost("password")
                    )
                ));
                if (!$user) {
                    $this->session->set('msg', "*Incorrect credentials");
                } else {
                    $this->session->set('msg', "Hello!!");
                    $this->session->set('email', $user->email);
                    $this->session->set('password', $user->password);
                    header('location:/index');
                }
            }
        }
    }
}
