<?php

namespace Ffcms\Core\Helper\Type;

/**
 * Class Integer. Helper for work with integers
 * @package Ffcms\Core\Helper\Type
 */
class Integer
{
    const MAX = 2147483647;

    /**
     * Random Integer with $sequence. Ex: randomInt(2) = 1..9 * 10 ^ 2
     * @param int $sequence - sequence of random calculation
     * @return int
     */
    public static function random(int $sequence): ?int
    {
        $start = pow(10, $sequence - 1);
        $end = pow(10, $sequence);
        return mt_rand($start, $end);
    }
}
