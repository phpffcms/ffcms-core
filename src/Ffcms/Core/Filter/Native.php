<?php

namespace Ffcms\Core\Filter;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Object;
use Ffcms\Core\Helper\Type\Str;

class Native
{

    /**
     * Filter ['object', 'length_min', 'length']
     * @param $object
     * @param $length
     * @return bool
     */
    public static function length_min($object, $length)
    {
        if (Object::isArray($object)) {
            return false;
        }
        return Str::length($object) >= $length;
    }

    /**
     * Filter ['object', 'length_max', 'length']
     * @param $object
     * @param $length
     * @return bool
     */
    public static function length_max($object, $length)
    {
        if (Object::isArray($object)) {
            return false;
        }
        return Str::length($object) <= $length;
    }

    /**
     * Filter ['object', 'in', 'handle']
     * @param $object
     * @param array $handle
     * @return bool
     */
    public static function in($object, array $handle)
    {
        if (Object::isArray($object)) {
            return false;
        }

        // allow empty, validate on required rule
        if (Str::likeEmpty($object)) {
            return true;
        }

        return Arr::in($object, $handle);
    }

    /**
     * Filter ['object', 'string']
     * @param $object
     * @return bool
     */
    public static function string($object)
    {
        return Object::isString($object);
    }

    /**
     * Filter ['object', 'arr']
     * @param $object
     * @return bool
     */
    public static function arr($object)
    {
        return Object::isArray($object);
    }

    /**
     * Filter ['object', 'int']
     * @param $object
     * @return bool
     */
    public static function int($object)
    {
        return Object::isLikeInt($object);
    }

    /**
     * Filter ['object', 'float']
     * @param $object
     * @return bool
     */
    public static function float($object)
    {
        return Object::isLikeFloat($object);
    }

    /**
     * Filter ['object', 'boolean']
     * @param $object
     * @return bool
     */
    public static function boolean($object)
    {
        return Object::isLikeBoolean($object);
    }

    /**
     * Filter ['object', 'required']
     * @param $object
     * @return bool
     */
    public static function required($object)
    {
        if (Object::isArray($object)) {
            return count($object) > 0;
        }
        return Str::length($object) > 0;
    }

    /**
     * Filter ['object', 'email']
     * @param $object
     * @return bool
     */
    public static function email($object)
    {
        if (Object::isArray($object)) {
            return false;
        }

        // allow empty, validate on required rule
        if (Str::likeEmpty($object)) {
            return true;
        }

        return Str::isEmail($object);
    }

    /**
     * Filter ['object', 'phone']
     * @param string $object
     * @return bool|int
     */
    public static function phone($object)
    {
        if (Object::isArray($object)) {
            return false;
        }

        // allow empty, validate on required rule
        if (Str::likeEmpty($object)) {
            return true;
        }

        return Str::isPhone($object);
    }

    /**
     * Filter ['object', 'url']
     * @param string $object
     * @return bool
     */
    public static function url($object)
    {
        if (Object::isArray($object)) {
            return false;
        }

        // allow empty, validate on required rule
        if (Str::likeEmpty($object)) {
            return true;
        }

        return Str::isUrl($object);
    }

    /**
     * Filter ['object', 'equal', value]
     * @param $object
     * @param $value
     * @return bool
     */
    public static function equal($object, $value = null)
    {
        if (Object::isArray($object)) {
            return false;
        }

        return $object === $value;
    }

    /**
     * Direct preg_match expression. Filter ['object', 'direct_match', '/^[A-Z]/*$']
     * @param $object
     * @param $value
     * @return bool|int
     */
    public static function direct_match($object, $value)
    {
        return self::reg_match($object, $value);
    }

    /**
     * Reverse preg_match expression. Filter ['object', 'reverse_match', '/^[A-Z]/*$']
     * @param $object
     * @param $value
     * @return bool
     */
    public static function reverse_match($object, $value) {
        return !self::reg_match($object, $value);
    }

    /**
     * Regular expression validation rule ['object', 'reg_match', '/^[A-Z]/*$']
     * @param $object
     * @param $value
     * @return bool|int
     */
    public static function reg_match($object, $value)
    {
        if (Object::isArray($object)) {
            return false;
        }

        if (Str::likeEmpty($object)) {
            return true;
        }

        return preg_match($value, $object) > 0;
    }

    /**
     * Filter ['object', 'intList']
     * @param string $object
     * @param $value
     * @return bool
     */
    public static function intList($object, $value)
    {
        if (Object::isArray($object)) {
            return false;
        }
        return !preg_match('/[^0-9, ]/', $object);
    }

    /**
     * @param object $object
     * @param $value
     * @return bool
     */
    public static function isFile($object, $value)
    {
        $all = false;
        // if string is given
        if (!Object::isArray($value)) {
            if ($value === '*') {
                $all = true;
            } else {
                $value = [$value];
            }
        }

        // input file is not object?
        if ($object === null || !Object::isObject($object)) {
            return false;
        }

        // get guess file type, based on mime-type
        $type = $object->guessExtension();
        if ($type === null) {
            return false;
        }

        return $all ? true : Arr::in($type, $value);
    }

    /**
     * @param object $object
     * @param $value
     * @return bool
     */
    public static function sizeFile($object, $value)
    {
        if (!Object::isArray($value)) {
            $value = [0, $value];
        }

        // input file is not object?
        if ($object === null || !Object::isObject($object)) {
            return false;
        }

        // get file upload size in bytes
        $realSize = $object->getClientSize();
        if ($realSize === null) {
            return false;
        }

        return $realSize > $value[0] && $realSize <= $value[1];
    }

}