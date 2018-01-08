<?php

namespace Ffcms\Core\Helper\FileSystem;

use Ffcms\Core\Helper\Type\Str;

/**
 * Class Normalize. Special system-based class who provide methods to make disk path readable and clean
 * @package Ffcms\Core\Helper\FileSystem
 */
class Normalize
{
    /**
     * Normalize local disk-based path. Ex: ../../dir/./dir/file.txt
     * @param string $path
     * @return string
     */
    public static function diskPath($path)
    {
        // its full-based path? Lets return real path
        if (Str::startsWith(root, $path)) {
            // fix path collisions if is not exist
            if (file_exists($path)) {
                return realpath($path);
            } else {
                return $path;
            }
        }
        // else - sounds like relative path
        $path = Str::replace('\\', '/', $path);
        $splitPath = explode('/', $path);

        $outputPath = [];
        foreach ($splitPath as $index => $part) {
            if ($part === '.' || Str::length(trim($part)) < 1) {
                continue;
            }

            if ($part === '..') { // level-up (:
                array_pop($outputPath);
                continue;
            }

            $outputPath[] = trim($part);
        }
        return implode(DIRECTORY_SEPARATOR, $outputPath);
    }

    /**
     * Normalize local disk-based ABSOLUTE path.
     * @param string $path
     * @return string
     */
    public static function diskFullPath($path)
    {
        $path = self::diskPath($path);
        if (!Str::startsWith(root, $path)) {
            $path = root . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
        }
        return $path;
    }
}
