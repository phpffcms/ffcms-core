<?php

namespace Ffcms\Core\Interfaces;

use Apps\ActiveRecord\Profile;
use Apps\ActiveRecord\Role;
use Apps\ActiveRecord\WallPost;

interface iUser
{
    /**
     * Get current user id if auth
     * @return int
     */
    public function getId();

    /**
     * Get user param
     * @param string $param
     * @param null|string $defaultValue
     * @return string|null
     */
    public function getParam($param, $defaultValue = null);

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
     * @return null|iUser
     */
    public static function identity($user_id = null);

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
     * @return iUser|null
     */
    public static function getIdentityViaEmail($email);

    /**
     * Get user wall post object
     * @return WallPost
     */
    public function getWall();

    /**
     * Get user role data
     * @return Role
     */
    public function getRole();

    /**
     * Get user profile data. Call like (new User())->Profile->column;
     * @return Profile
     */
    public function getProfile();
}