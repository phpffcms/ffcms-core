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
     * Get user name by user id with predefined value on empty or not exist profile
     * @param $userId
     * @param string $onEmpty
     * @return string
     */
    public static function parseUserName($userId = null, $onEmpty = 'guest'): ?string
    {
        if (!Any::isInt($userId)) {
            return \App::$Security->strip_tags($onEmpty);
        }

        // try to find user active record as object
        $identity = App::$User->identity($userId);
        if (!$identity) {
            return \App::$Security->strip_tags($onEmpty);
        }

        // return user name from profile
        return $identity->profile->getName();
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
        $name = self::parseUserName($userId, $onEmpty);
        // new name is not found, lets return default
        if ($name === $onEmpty || (int)$userId < 1) {
            return $name;
        }

        return Url::a([$controllerAction, [(int)$userId]], $name);
    }
}
