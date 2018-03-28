<?php

namespace Ffcms\Core\Interfaces;

/**
 * Interface iUser
 * @package Ffcms\Core\Interfaces
 * @property int $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property int $role_id
 * @property string $approve_token
 */
interface iUser
{
    /**
     * Get current user id if auth
     * @return int
     */
    public function getId(): ?int;

    /**
     * Get user param
     * @param string $param
     * @param null|string $defaultValue
     * @return string|null
     */
    public function getParam(string $param, ?string $defaultValue = null): ?string;

    /**
     * Check if current user session is auth
     * @return bool
     */
    public static function isAuth(): bool;

    /**
     * Check if user with $id exist
     * @param string|int|null $id
     * @return bool
     */
    public static function isExist(?string $id = null): bool;

    /**
     * Get user person all data like a object
     * @param string|int|null $id
     * @return null|self
     */
    public static function identity(?string $id = null);

    /**
     * Check if use with $email is exist
     * @param string|null $email
     * @return bool
     */
    public static function isMailExist(?string $email = null): bool;

    /**
     * Check if user with $login is exist
     * @param string|null $login
     * @return bool
     */
    public static function isLoginExist(?string $login = null): bool;

    /**
     * Get user person like a object via email
     * @param string|null $email
     * @return null|self
     */
    public static function getIdentityViaEmail(?string $email = null);


    /**
     * Check if target user with $target_id in blacklist for current session user_id
     * @param string|int|bool $target
     * @return bool
     */
    public function inBlacklist(?string $target = null): bool;
}
