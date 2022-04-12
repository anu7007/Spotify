<?php

use Phalcon\Mvc\Controller;

class SignupController extends Controller
{
    public function indexAction()
    {
      
        $user = new Users();
        $postdata = $this->request->getPost();
        if ($this->request->getPost()) {
            $user->assign(
                $postdata,
                [
                    'full_name',
                    'username',
                    'email',
                    'password',
                    'confirm_password',
                    'gender',
                    'dob'

                ]
            );
            if ($postdata['password'] != $postdata['confirm_password']) {
                $this->view->passwordError = "Passwords don't match" . "<br>";
            }
            else {
                $success = $user->save();
                $this->view->success = $success;
            }
            if ($success) {
                $this->view->message = "Register succesfully";
            } else {
                $this->view->message = "Not Register succesfully due to following reason: <br>" . implode("<br>", $user->getMessages());
            }
        }
    }
}
