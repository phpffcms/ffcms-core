<?php

namespace Ffcms\Core\Interfaces;

interface iUser
{
    /**
     * Get current user id if auth
     * @return int|null
     */
    public function getId();

    /**
     * Get user param
     * @param string $param
     * @param null|int $custom_id
     * @return mixed|null
     */
    public function get($param, $custom_id = null);

    /**
     * Check if current user session is auth
     * @return bool
     */
    public function isAuth();

    /**
     * Check if user with $id exist
     * @param int $id
     * @return bool|int
     */
    public function isExist($id);

    /**
     * Get user person all data like a object
     * @param null|int $custom_id
     * @return bool|\Illuminate\Support\Collection|null|static
     */
    public function getPerson($custom_id = null);

    /**
     * Check if use with $email is exist
     * @param string $email
     * @return bool
     */
    public function isMailExist($email);

    /**
     * Check if user with $login is exist
     * @param string $login
     * @return bool
     */
    public function isLoginExist($login);

    /**
     * Get user person like a object via email
     * @param string $email
     * @return bool
     */
    public function getPersonViaEmail($email);
}