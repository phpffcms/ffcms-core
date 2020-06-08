<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class ModelFilters. Collection of native static methods for model filters.
 * This methods can be used in models, methods rules() as second argument in array.
 * @package Ffcms\Core\Helper
 */
class ModelFilters
{

    /**
     * Filter ['object', 'length_min', 'length']
     * @param $object
     * @param $length
     * @return bool
     */
    public static function length_min($object, $length): bool
    {
        if (!Any::isLine($object)) {
            return false;
        }

        return Str::length((string)$object) >= $length;
    }

    /**
     * Filter ['object', 'length_max', 'length']
     * @param $object
     * @param $length
     * @return bool
     */
    public static function length_max($object, $length): bool
    {
        if (!Any::isLine($object)) {
            return false;
        }

        return Str::length((string)$object) <= $length;
    }

    /**
     * Filter ['object', 'in', ['handles']]
     * @param string $object
     * @param array|null $handle
     * @return bool
     */
    public static function in($object, array $handle): bool
    {
        if (!Any::isLine($object)) {
            return false;
        }

        // allow empty, validate on required rule
        if (Any::isEmpty($object)) {
            return true;
        }

        $object = Any::guessValueType($object);

        $res = Arr::in($object, $handle);
        return $res;
    }

    /**
     * Filter ['object', 'notin', ['handles']]
     * @param string $object
     * @param array $handle
     * @return bool
     */
    public static function notin($object, array $handle): bool
    {
        return !self::in($object, $handle);
    }

    /**
     * Filter ['object', 'checked']
     * @param $object
     * @return bool
     */
    public static function checked($object): bool
    {
        if (!Any::isBool($object)) {
            return false;
        }

        return (bool)$object;
    }

    /**
     * Filter ['object', 'string']
     * @param $object
     * @return bool
     */
    public static function string($object): bool
    {
        return Any::isStr($object);
    }

    /**
     * Filter ['object', 'arr']
     * @param $object
     * @return bool
     */
    public static function arr($object): bool
    {
        return Any::isArray($object);
    }

    /**
     * Filter ['object', 'int']
     * @param $object
     * @return bool
     */
    public static function int($object): bool
    {
        return Any::isInt($object) || Any::isEmpty($object);
    }

    /**
     * Filter ['object', 'float']
     * @param $object
     * @return bool
     */
    public static function float($object): bool
    {
        return Any::isFloat($object);
    }

    /**
     * Filter ['object', 'boolean']
     * @param $object
     * @return bool
     */
    public static function boolean($object): bool
    {
        return Any::isBool($object);
    }

    /**
     * Filter ['object', 'required']
     * @param $object
     * @return bool
     */
    public static function required($object): bool
    {
        return !Any::isEmpty($object);
    }

    /**
     * Filter ['object', 'email']
     * @param $object
     * @return bool
     */
    public static function email($object): bool
    {
        if (Any::isEmpty($object)) { // allow empty
            return true;
        }

        if (!Any::isLine($object)) {
            return false;
        }

        return Str::isEmail($object);
    }

    /**
     * Filter ['object', 'phone']
     * @param string $object
     * @return bool|int
     */
    public static function phone($object): bool
    {
        if (Any::isEmpty($object)) { // allow empty
            return true;
        }

        if (!Any::isLine($object)) {
            return false;
        }

        return Str::isPhone($object);
    }

    /**
     * Filter ['object', 'url']
     * @param string $object
     * @return bool
     */
    public static function url($object): bool
    {
        if (Any::isEmpty($object)) { // allow empty
            return true;
        }

        if (!Any::isLine($object)) {
            return false;
        }

        return Str::isUrl($object);
    }

    /**
     * Filter ['object', 'ipv4']
     * @param string $object 
     * @return bool 
     */
    public static function ipv4($object): bool
    {
        if (!Any::isStr($object) || Any::isEmpty($object)) {
            return false;
        }

        return filter_var($object, FILTER_VALIDATE_IP);
    }

    /**
     * Filter ['object', 'equal', value]. Check if input data is equals to value
     * @param string $object
     * @param $value
     * @return bool
     */
    public static function equal($object, $value = null): bool
    {
        if (!Any::isLine($object)) {
            return false;
        }

        return $object === $value;
    }

    /**
     * Filter ['object', 'notequal', value]. Check if input data not equals to some value
     * @param string $object
     * @param string $value
     * @return bool
     */
    public static function notequal($object, $value = null): bool
    {
        if (!Any::isLine($object)) {
            return false;
        }

        return $object !== $value;
    }

    /**
     * Direct preg_match expression. Filter ['object', 'direct_match', '/^[A-Z]/*$']
     * @param $object
     * @param $value
     * @return bool
     */
    public static function direct_match($object, $value): bool
    {
        return self::reg_match($object, $value);
    }

    /**
     * Reverse preg_match expression. Filter ['object', 'reverse_match', '/^[A-Z]/*$']
     * @param $object
     * @param $value
     * @return bool
     */
    public static function reverse_match($object, $value): bool
    {
        return !self::reg_match($object, $value);
    }

    /**
     * Regular expression validation rule ['object', 'reg_match', '/^[A-Z]/*$']
     * @param $object
     * @param $value
     * @return bool
     */
    public static function reg_match($object, $value): bool
    {
        if (!Any::isLine($object)) {
            return false;
        }

        if (Str::likeEmpty($object)) {
            return true;
        }

        // what the f*ck? -> return preg_match($value, $object) > 0;
        return (bool)preg_match($value, $object);
    }

    /**
     * Filter ['object', 'intList']
     * @param string $object
     * @param $value
     * @return bool
     */
    public static function intList($object, $value): bool
    {
        if (!Any::isLine($object)) {
            return false;
        }

        return !preg_match('/[^0-9\s,]/$', $object);
    }

    /**
     * Check if field is file or null
     * @param object $object
     * @param $value
     * @return bool
     */
    public static function isFile($object, $value): bool
    {
        // allow empty fields, "required" option filter that
        if ($object === null) {
            return true;
        }

        $all = false;
        // if string is given
        if (!Any::isArray($value)) {
            if ($value === '*') {
                $all = true;
            } else {
                $value = [$value];
            }
        }

        // input file is not object?
        if ($object === null || !Any::isObj($object)) {
            return false;
        }

        // get guess file type, based on mime-type
        $type = $object->guessExtension();
        if ($type === null) {
            return false;
        }

        return ($all ? true : Arr::in($type, $value));
    }

    /**
     * Check file size. If is null - will return true
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $object
     * @param $value
     * @return bool
     */
    public static function sizeFile($object, $value): bool
    {
        // allow empty field, validate on filter 'required'
        if ($object === null) {
            return true;
        }

        if (!Any::isArray($value)) {
            $value = [0, $value];
        }

        // input file is not object?
        if ($object === null || !Any::isObj($object)) {
            return false;
        }

        // get file upload size in bytes
        $realSize = $object->getSize();
        if ($realSize === null) {
            return false;
        }

        return $realSize > $value[0] && $realSize <= $value[1];
    }
}
