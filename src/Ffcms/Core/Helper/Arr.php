<?php

namespace Ffcms\Core\Helper;

class Arr
{

    /**
     * Check is $needle in $haystack. Alias to function in_array()
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
}