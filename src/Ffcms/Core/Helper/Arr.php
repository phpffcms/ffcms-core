<?php

namespace Ffcms\Core\Helper;

class Arr
{

    /**
     * Check is $needle in $haystack. Alias to function in_array().
     * @param string $needle
     * @param array $haystack
     * @param bool $strict
     * @return bool
     */
    public static function in($needle, array $haystack, $strict = true)
    {
        return in_array($needle, $haystack, $strict);
    }

    /**
     * Alternative function for array_merge - safe for use with any-type params.
     * @return array
     */
    public static function merge()
    {
        $arguments = [];
        foreach (func_get_args() as $key => $val) {
            if (!Object::isArray($val)) {
                $val = [];
            }
            $arguments[$key] = $val;
        }
        return call_user_func_array('array_merge', $arguments);
    }

    /**
     * Get array item by path separated by dots. Example: getByPath('dir.file', ['dir' => ['file' => 'text.txt']]) return "text.txt"
     * @param string $path
     * @param array|null $array
     * @param string $delimiter
     * @return array|string|null
     */
    public static function getByPath($path, array $array = null, $delimiter = '.')
    {
        // path of nothing? interest
        if (count($array) < 1) {
            return null;
        }

        // c'mon man, what the f*ck are you doing? ))
        if (!String::contains($delimiter, $path)) {
            return $array[$path];
        }

        $output = $array;
        $pathArray = explode($delimiter, $path);
        foreach ($pathArray as $key) {
            $output = $output[$key];
        }
        return $output;
    }
}