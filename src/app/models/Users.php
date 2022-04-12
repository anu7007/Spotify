<?php

use Phalcon\Mvc\Model;

class Users extends Model
{
    public $id;
    public $full_name;
    public $username;
    public $email;
    public $password;
    public $confirm_password;
}