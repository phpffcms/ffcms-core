<?php

namespace Ffcms\Core\Helper\FileSystem;

use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class File. Provide methods to work with files in current filesystem.
 * @package Ffcms\Core\Helper
 */
class File
{

    /**
     * Read file content from local storage
     * @param $path
     * @return bool|string
     */
    public static function read($path)
    {
        $path = Normalize::diskFullPath($path);

        if (!self::exist($path)) {
            return false;
        }

        return @file_get_contents($path);
    }

    /**
     * Check if $path is exist and readable in filesystem
     * @param string $path
     * @return bool
     */
    public static function exist($path)
    {
        $path = Normalize::diskFullPath($path);
        return (file_exists($path) && is_readable($path) && is_file($path));
    }

    /**
     * Alias for exist method
     * @param string $path
     * @return bool
     */
    public static function readable($path) {
        return self::exist($path);
    }

    /**
     * Check is file writable
     * @param string $path
     * @return bool
     */
    public static function writable($path)
    {
        $path = Normalize::diskFullPath($path);

        if (!self::exist($path)) {
            return false;
        }

        return is_writable($path);
    }

    /**
     * Check is file executable
     * @param string $path
     * @return bool
     */
    public static function executable($path)
    {
        $path = Normalize::diskFullPath($path);

        if (!self::exist($path)) {
            return false;
        }

        return is_executable($path);
    }

    /**
     * @param string $path
     * @param null|string $content
     * @param null|int $flags
     * @return int
     */
    public static function write($path, $content = null, $flags = null)
    {
        $path = Normalize::diskFullPath($path);

        $pathArray = explode(DIRECTORY_SEPARATOR, $path);
        array_pop($pathArray);
        $pathName = implode(DIRECTORY_SEPARATOR, $pathArray);

        if (Directory::exist($pathName)) {
            Directory::create($pathName);
        }
        return @file_put_contents($path, $content, $flags);
    }

    /**
     * Remove file
     * @param string $path
     * @return bool
     */
    public static function remove($path)
    {
        $path = Normalize::diskFullPath($path);

        if (!self::exist($path)) {
            return false;
        }
        return unlink($path);
    }


    /**
     * Alternative of functions include, require, include_once and etc in 1 function
     * @param string $path
     * @param bool|false $return
     * @param bool|false $once
     * @return bool|mixed
     */
    public static function inc($path, $return = false, $once = false)
    {
        $path = Normalize::diskFullPath($path);

        if (!self::exist($path)) {
            return false;
        }

        if ($return === true) {
            return $once === true ? require_once($path) : require $path;
        } else {
            ($once == true) ? require_once($path) : require $path;
        }
    }

    /**
     * Get file make time in unix timestamp
     * @param string $path
     * @return int
     */
    public static function mTime($path)
    {
        $path = Normalize::diskFullPath($path);
        if (!self::exist($path)) {
            return 0;
        }

        return filemtime($path);
    }

    /**
     * Recursive scan directory, based on $path and allowed extensions $ext or without it
     * @param string $path
     * @param array $ext
     * @param bool $returnRelative
     * @param $files
     * @return array
     */
    public static function listFiles($path, array $ext = null, $returnRelative = false, &$files = [])
    {
        $path = Normalize::diskFullPath($path);

        if (!Directory::exist($path)) {
            return [];
        }

        $dir = opendir($path . '/.');
        while ($item = readdir($dir)) {
            if (is_file($sub = $path . '/' . $item)) {
                $item_ext = Str::lastIn($item, '.');
                if ($ext === null || Arr::in($item_ext, $ext)) {
                    if ($returnRelative) {
                        $files[] = $item;
                    } else {
                        $files[] = $path . DIRECTORY_SEPARATOR . $item;
                    }
                }
            } else {
                if ($item !== '.' && $item !== '..') {
                    self::listFiles($sub, $ext, $returnRelative, $files);
                }
            }
        }

        return $files;
    }

    /**
     * Get file size in bytes
     * @param string $path
     * @return int
     */
    public static function size($path)
    {
        $path = Normalize::diskFullPath($path);

        if (!self::exist($path)) {
            return 0;
        }

        return filesize($path);
    }

}