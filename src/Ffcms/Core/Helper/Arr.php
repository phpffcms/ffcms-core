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
}