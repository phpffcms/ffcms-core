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
     * @param null|string $defaultValue
     * @return string|null
     */
    public function get($param, $defaultValue = null);

    /**
     * @param $param
     * @param null|string $defaultValue
     * @return string|null
     */
    public function getCustomParam($param, $defaultValue = null);

    /**
     * Check if current user session is auth
     * @return bool
     */
    public static function isAuth();

    /**
     * Check if user with $id exist
     * @param int $id
     * @return bool
     */
    public static function isExist($id);

    /**
     * Get user person all data like a object
     * @param null|int $user_id
     * @return bool|\Illuminate\Support\Collection|null|static
     */
    public static function identity($user_id);

    /**
     * Check if use with $email is exist
     * @param string $email
     * @return bool
     */
    public static function isMailExist($email);

    /**
     * Check if user with $login is exist
     * @param string $login
     * @return bool
     */
    public static function isLoginExist($login);

    /**
     * Get user person like a object via email
     * @param string $email
     * @return bool
     */
    public static function getIdentifyViaEmail($email);

    /**
     * Get user wall post object
     * @return object
     */
    public function getWall();

    /**
     * Get user avatar path
     * @return string
     */
    public function getAvatarUrl();
}