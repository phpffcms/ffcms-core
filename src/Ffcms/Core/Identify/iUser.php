<?php

namespace Ffcms\Core\Identify;

interface iUser
{
    public function getId();

    public function get($param, $custom_id = null);

    public function isAuth();

    public function isExist($id);

    public function getSession($custom_id = null);

    public function isMailExist($email);

    public function isLoginExist($login);

    public function getPersonViaEmail($email);

}