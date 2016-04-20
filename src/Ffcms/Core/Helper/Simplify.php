<?php

namespace Ffcms\Core\Helper;


use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Obj;

/**
 * Class Simplify. Simplification of ofter used logic and methods.
 * @package Ffcms\Core\Helper
 */
class Simplify
{
    /**
     * Get user nickname by user id with predefined value on empty or not exist profile
     * @param $userId
     * @param string $onEmpty
     * @return string
     */
    public static function parseUserNick($userId = null, $onEmpty = 'guest')
    {
        // try to get user id as integer
        if (Obj::isLikeInt($userId)) {
            $userId = (int)$userId;
        } else { // user id is empty, lets return default value
            return \App::$Security->strip_tags($onEmpty);
        }

        // try to find user active record as object
        $identity = App::$User->identity($userId);
        if ($identity === null || $identity === false) {
            return \App::$Security->strip_tags($onEmpty);
        }

        // return user nickname from profile
        return $identity->getProfile()->getNickname();
    }

    /**
     * Prepare HTML DOM link (a href) to user with him name or guest name without link
     * @param int $userId
     * @param string $onEmpty
     * @param string $controllerAction
     * @return string
     */
    public static function parseUserLink($userId = null, $onEmpty = 'guest', $controllerAction = 'profile/show')
    {
        $nick = self::parseUserNick($userId, $onEmpty);
        // new name is not found, lets return default
        if ($nick === $onEmpty || (int)$userId < 1) {
            return $nick;
        }

        return Url::link([$controllerAction, (int)$userId], $nick);
    }

}