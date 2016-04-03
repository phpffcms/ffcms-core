<?php

namespace Ffcms\Core\Helper\Type;

class Obj
{
    /**
     * Check if input data is integer
     * @param mixed $data
     * @return bool
     */
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

    /**
     * Check if $data is string type
     * @param mixed $data
     * @return bool
     */
    public static function isString($data)
    {
        return is_string($data);
    }

    /**
     * Check if $data is array type
     * @param mixed $data
     * @return bool
     */
    public static function isArray($data)
    {
        return is_array($data);
    }

    /**
     * Check if $data is float
     * @param $data
     * @return bool
     */
    public static function isFloat($data)
    {
        return is_float($data);
    }

    /**
     * Check is $data look's like float
     * @param $data
     * @return bool
     */
    public static function isLikeFloat($data)
    {
        return false !== filter_var($data, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Check is $data look's like boolean
     * @param $data
     * @return bool
     */
    public static function isLikeBoolean($data)
    {
        // will return true for "1", true, "on", "yes", false for 0, "false", "off", 'no', '', NULL FOR ANY OTHER VALUE!!!
        return filter_var($data, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
    }

    /**
     * Check if $data is object
     * @param $data
     * @return bool
     */
    public static function isObject($data)
    {
        return is_object($data);
    }
}