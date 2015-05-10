<?php

namespace Ffcms\Core\Helper;


/**
 * Class File - helper to work with files
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
        return (file_exists($path) && is_readable($path));
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

}