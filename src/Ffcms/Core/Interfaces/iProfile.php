<?php

namespace Ffcms\Core\Interfaces;

interface iProfile
{
    /**
     * Get user profile via user_id like object (!!! profile.id !== user.id !!!)
     * @param int|null $user_id
     * @return self|null
     */
    public static function identity($user_id = null);

    /**
     * Get user avatar full url for current object
     * @param string $type
     * @return string
     */
    public function getAvatarUrl($type = 'small');

    /**
     * Get user identity for current object
     * @return iUser|null
     */
    public function User();
}