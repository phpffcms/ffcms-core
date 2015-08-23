<?php

namespace Ffcms\Core\Helper\FileSystem;

use Ffcms\Core\Helper\Type\String;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Directory
{
    /**
     * Check if directory is exist and readable. Alias for File::exist()
     * @param string $path
     * @return bool
     */
    public static function exist($path)
    {
        $path = Normalize::diskFullPath($path);

        return (file_exists($path) && is_readable($path) && is_dir($path));
    }

    /**
     * Create directory with recursive support.
     * @param string $path
     * @param int $chmod
     * @return bool
     */
    public static function create($path, $chmod = 0755)
    {
        $path = Normalize::diskFullPath($path);

        if (self::exist($path)) {
            return false;
        }

        return @mkdir($path, $chmod, true);
    }

    /**
     * Remove directory recursive.
     * @param string $path
     * @return bool
     */
    public static function remove($path)
    {
        $path = Normalize::diskFullPath($path);

        if (!self::exist($path)) {
            return false;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $dir) {
            $dir->isFile() ? @unlink($dir->getPathname()) : @rmdir($dir->getPathname());
        }
        return @rmdir($path);
    }

    /**
     * Scan files in directory and return full or relative path
     * @param string $path
     * @param int $mod
     * @param bool|false $returnRelative
     * @return array|bool
     */
    public static function scan($path, $mod = GLOB_ONLYDIR, $returnRelative = false)
    {
        $path = Normalize::diskFullPath($path);

        if (!self::exist($path)) {
            return false;
        }

        $pattern = rtrim($path, '/') . '/*';
        $entry = glob($pattern, $mod);

        if ($returnRelative === true) {
            foreach ($entry as $key => $value) {
                $entry[$key] = trim(str_replace($path, null, $value), '/');
            }
        }

        return $entry;
    }

    /**
     * Rename directory based only on new name for last element. Example: rename('/var/www/html', 'php') => /var/www/php
     * @param string $path
     * @param string $newDirName
     * @return bool
     */
    public static function rename($path, $newDirName)
    {
        $path = Normalize::diskFullPath($path);

        if (!self::exist($path) || !is_writable($path)) {
            return false;
        }

        $path = rtrim($path, '/');
        $separatedPath = explode('/', $path);
        array_pop($separatedPath);
        $clearPath = implode('/', $separatedPath);
        @rename($path, $clearPath . '/' . $newDirName);

        return true;
    }
}