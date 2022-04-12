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
                    // return $this->dispatcher->forward(array( 
                    //    'controller' => 'index', 'action' => 'index' 
                    // ));
                }
                //  $this->session->set('auth', $user->user_id); 
                else {
                    if ($user->status == "pending" && $user->role == "user") {
                        $this->session->set('msg', "!! Your Request is in Queue !!<br>Kindly wait for Approval<br><br>");
                    } elseif ($user->status == "approved" && $user->role == "user") {
                        $this->session->set('msg', "You are a user and with approval");
                        $this->session->set('activeUser', $user->full_name);
                        $this->session->set('activeRole', $user->role);
                        header('location:/user');
                    } else {
                        $this->session->set('msg', "Hello ADMIN !!");
                        $this->session->set('activeUser', $user->full_name);
                        $this->session->set('activeRole', $user->role);
                        header('location:/admin');
                    }


                    // header("location: localhost:8080/login");
                }
            }
        }
    }
}
