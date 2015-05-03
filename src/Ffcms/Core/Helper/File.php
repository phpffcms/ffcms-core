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
        if (!file_exists($path) || is_readable($path)) {
            return false;
        }
        return @file_get_contents($path);
    }

}