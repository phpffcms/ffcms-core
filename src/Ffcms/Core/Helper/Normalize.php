<?php

namespace Ffcms\Core\Helper;

class Normalize
{
    /**
     * Normalize local disk-based path. Ex: ../../dir/./dir/file.txt
     * @param string $path
     * @return string
     */
    public static function diskPath($path)
    {
        $path = String::replace('\\', '/', $path);
        $splitPath = explode('/', $path);

        $outputPath = [];
        foreach ($splitPath as $index => $part) {
            if ($part === '.' || String::length(trim($part)) < 1) {
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
        if (!String::startsWith(root, $path)) {
            $path = root . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
        }
        return $path;
    }
}