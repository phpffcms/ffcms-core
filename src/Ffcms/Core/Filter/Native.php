<?php

namespace Ffcms\Core\Filter;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Arr;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;

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
        return String::length($object) >= $length;
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
        return String::length($object) <= $length;
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
        return String::length($object) > 0;
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
        return String::isEmail($object);
    }

    /**
     * Filter ['object', 'equal', value]
     * @param $object
     * @param $value
     * @return bool
     */
    public static function equal($object, $value)
    {
        if (Object::isArray($object)) {
            return false;
        }

        return $object === $value;
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