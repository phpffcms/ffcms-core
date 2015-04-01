<?php

namespace Ffcms\Core\Helper;

class Integer {

    const MAX = 2147483647;


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