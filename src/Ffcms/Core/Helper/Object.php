<?php

namespace Ffcms\Core\Helper;

class Object {


    public static function isInt($data)
    {
        return is_int($data);
    }

    /**
     * Check is current variable seems like integer. Example - string variable only with integer values.
     * @param mixed $data
     * @return bool
     */
    public static function isLikeInt($data)
    {
        return filter_var($data, FILTER_VALIDATE_INT) !== false;
    }
}