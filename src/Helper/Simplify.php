<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Templex\Url\Url;

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
    public static function parseUserNick($userId = null, $onEmpty = 'guest'): ?string
    {
        if (!Any::isInt($userId)) {
            return \App::$Security->strip_tags($onEmpty);
        }

        // try to find user active record as object
        $identity = App::$User->identity($userId);
        if (!$identity) {
            return \App::$Security->strip_tags($onEmpty);
        }

        // return user nickname from profile
        return $identity->profile->getNickname();
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

        return Url::a([$controllerAction, [(int)$userId]], $nick);
    }
}
