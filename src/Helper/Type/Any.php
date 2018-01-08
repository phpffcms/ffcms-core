<?php

namespace Ffcms\Core\Helper\Type;

/**
 * Class Any. Work with unknown variable types. Some magic and drugs inside. Use careful!
 * @package Ffcms\Core\Helper\Type
 */
class Any
{
    /**
     * Check if unknown type variable is looks like empty
     * @param mixed $var
     * @return bool
     */
    public static function isEmpty($var = null): bool
    {
        // var is array type?
        if (is_array($var)) {
            return count($var) < 1;
        }

        // var seems to be object?
        if (is_object($var)) {
            return count(get_object_vars($var)) < 1;
        }

        // float,int,string,bool and null left. Check if not empty. Int and float will never equal to null.
        return ($var === null || $var === '' || $var === false);
    }

    /**
     * Check if $var is int or looks like integer and parseInt by ref
     * @param mixed $var
     * @return bool
     */
    public static function isInt(&$var = null): bool
    {
        $parse = filter_var($var, FILTER_VALIDATE_INT);
        if ($parse !== false) {
            $var = $parse;
            return true;
        }

        return false;
    }

    /**
     * Check if $var is string
     * @param mixed $var
     * @return bool
     */
    public static function isStr($var = null): bool
    {
        return is_string($var);
    }

    /**
     * Check if $var is like float and parseFloat by ref
     * @param mixed $var
     * @return bool
     */
    public static function isFloat(&$var = null): bool
    {
        $parse = filter_var($var, FILTER_VALIDATE_FLOAT);
        if ($parse !== false) {
            $var = $parse;
            return true;
        }
        return false;
    }

    /**
     * Check if $var is array type
     * @param mixed $var
     * @return bool
     */
    public static function isArray($var = null): bool
    {
        return is_array($var);
    }

    /**
     * Check if $var is iterable
     * @param mixed $var
     * @return bool
     */
    public static function isIterable($var = null): bool
    {
        return is_iterable($var);
    }

    /**
     * Check if var is boolean and parseBool by ref
     * @param mixed $var
     * @return bool
     */
    public static function isBool(&$var = null): bool
    {
        // will return true for "1", true, "on", "yes", false for 0, "false", "off", 'no', '', NULL FOR ANY OTHER VALUE!!!
        $parse = filter_var($var, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($parse !== null) {
            $var = $parse;
            return true;
        }
        return false;
    }

    /**
     * Check if $var is object type
     * @param mixed $var
     * @return bool
     */
    public static function isObj($var = null): bool
    {
        return is_object($var);
    }

    /**
     * Guest var value by strict type
     * @param mixed $var
     * @return mixed
     */
    public static function guessValueType($var = null)
    {
        // pass var by reference and guess var type
        if (self::isInt($var)) {
            return $var;
        }

        if (self::isFloat($var)) {
            return $var;
        }

        if (self::isBool($var)) {
            return $var;
        }


        return $var;
    }

    /**
     * Check if $var is "line" type based: string,int,float,null
     * @param mixed $var
     * @return bool
     */
    public static function isLine($var = null): bool
    {
        return (!is_object($var) && !is_iterable($var));
    }
}
