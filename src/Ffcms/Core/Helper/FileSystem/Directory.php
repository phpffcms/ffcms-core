<?php

namespace Ffcms\Core\Helper\FileSystem;

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
                $entry[$key] = str_replace($path, null, $value);
            }
        }

        return $entry;
    }
}