<?php

namespace Ffcms\Core\Helper;

class Integer {

    const MAX = 2147483647;

    /**
     * Check is current variable is integer
     * @param int $integer
     * @return bool
     */
    public static function is($integer)
    {
        return is_int($integer);
    }

    /**
     * Check is current variable seems like integer. Example - string variable only with integer values.
     * @param mixed $integer
     * @return bool
     */
    public static function isLike($integer)
    {
        return filter_var($integer, FILTER_VALIDATE_INT) !== false;
    }


    /**
     * Random Integer with $sequence. Ex: randomInt(2) = 1..9 * 10 ^ 2
     * @param Integer $sequence - sequence of random calculation
     * @return number
     */
    public static function random($sequence)
    {
        $start = pow(10, $sequence - 1);
        $end = pow(10, $sequence);
        return rand($start, $end);
    }
}