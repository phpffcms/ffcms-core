<?php


namespace Core\Helper;

class String {

    /**
     * Check is $where starts with prefix $string
     * @param string $string
     * @param string $where
     * @return bool
     */
    public static function startsWith($string, $where)
    {
        // check is not empty string
        if(self::length($string) < 1 || self::length($where) < 1)
        {
            return false;
        }
        $pharse_prefix = mb_substr($where, 0, self::length($string), "UTF-8");
        return $pharse_prefix === $string ? true : false;
    }

    /**
     * Check is $where ends with suffix $string
     * @param string $string
     * @param string $where
     * @return bool
     */
    public static function endsWith($string, $where)
    {
        // check is not empty string
        if(self::length($string) < 1 || self::length($where))
        {
            return false;
        }
        $pharse_suffix = mb_substr($where, -self::length($string), "UTF-8");
        return $pharse_suffix === $string ? true : false;
    }


    /**
     * Calculate $string length according UTF-8 encoding
     * @param string $string
     * @return int
     */
    public static function length($string)
    {
        return mb_strlen($string, "UTF-8");
    }

}