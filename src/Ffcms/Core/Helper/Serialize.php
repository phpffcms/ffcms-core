<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;

/**
 * Serialization and unsertialization data for database/any storage
 * Class Serialize
 * @package Ffcms\Core\Helper
 */
class Serialize
{

    /**
     * Serialize any data to special string (save to db/etc)
     * @param $data
     * @return string
     */
    public static function encode($data)
    {
        return serialize($data);
    }

    /**
     * Unserialize encoded data from string to object/array/etc
     * @param string $data
     * @return string|array
     */
    public static function decode($data)
    {
        return unserialize($data);
    }

    /**
     * Decode string $data and get value by key
     * @param $data
     * @param string $key
     * @return string|array|null
     */
    public static function getDecoded($data, $key)
    {
        $data = self::decode($data);
        return $data[$key];
    }

    /**
     * Decode serialized data based on current language as key
     * @param $data
     * @return array|null|string
     */
    public static function getDecodeLocale($data)
    {
        return self::getDecoded($data, App::$Request->getLanguage());
    }
}