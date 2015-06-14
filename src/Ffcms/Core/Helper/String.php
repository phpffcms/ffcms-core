<?php


namespace Ffcms\Core\Helper;

class String
{

    /**
     * Check is $where starts with prefix $string
     * @param string $string
     * @param string $where
     * @return bool
     */
    public static function startsWith($string, $where)
    {
        // check is not empty string
        if (self::length($string) < 1 || self::length($where) < 1) {
            return false;
        }
        $pharse_prefix = self::substr($where, 0, self::length($string));
        return $pharse_prefix === $string;
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
        if (self::length($string) < 1 || self::length($where) < 1) {
            return false;
        }
        $pharse_suffix = self::substr($where, -self::length($string));
        return $pharse_suffix === $string;
    }

    /**
     * Find first entry in $string before $delimiter
     * @param string $string
     * @param string $delimiter
     * @return string
     */
    public static function firstIn($string, $delimiter)
    {
        return strstr($string, $delimiter, true);
    }

    /**
     * Find latest entry in $string after $delimiter
     * @param string $string
     * @param string $delimiter
     * @param bool $withoutDelimiter
     * @return null|string
     */
    public static function lastIn($string, $delimiter, $withoutDelimiter = false)
    {
        $pos = strrpos($string, $delimiter);
        // if entry is not founded return null
        if (false === $pos) {
            return null;
        }
        // remove delimiter pointer
        if (true === $withoutDelimiter) {
            ++$pos;
        }

        return self::substr($string, $pos);
    }


    /**
     * Calculate $string length according UTF-8 encoding
     * @param string $string
     * @return int
     */
    public static function length($string)
    {
        return mb_strlen($string, 'UTF-8');
    }

    /**
     * Change content to lower case. Analog of strtolower with UTF-8
     * @param string $string
     * @return string
     */
    public static function lowerCase($string)
    {
        return mb_strtolower($string, 'UTF-8');
    }

    /**
     * Change content to upper case. Analog of strtoupper with UTF-8
     * @param string $string
     * @return string
     */
    public static function upperCase($string)
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    /**
     * Split camel case words with glue. camelCaseWords => camel Case Words
     * @param string $string
     * @param string $glue
     * @return string
     */
    public static function splitCamelCase($string, $glue = ' ')
    {
        $expression = '/(?#! splitCamelCase Rev:20140412)
                        # Split camelCase "words". Two global alternatives. Either g1of2:
                          (?<=[a-z])      # Position is after a lowercase,
                          (?=[A-Z])       # and before an uppercase letter.
                        | (?<=[A-Z])      # Or g2of2; Position is after uppercase,
                          (?=[A-Z][a-z])  # and before upper-then-lower case.
                        /x';
        $split = preg_split($expression, $string);
        $output = [];
        for ($i = 0; $i < count($split); ++$i) {
            $word = strtolower($split[$i]);
            if ($i === 0) {
                $word = ucfirst($word);
            }
            $output[] = $word;
        }

        return implode($glue, $output);
    }

    /**
     * Alias for mb_substr() function
     * @param string $string
     * @param int $start
     * @param int|null $length
     * @param string $encode
     * @return string
     */
    public static function substr($string, $start, $length = null, $encode = 'UTF-8')
    {
        return mb_substr($string, $start, $length, $encode);
    }

    /**
     * Alias for function str_replace()
     * @param string|array|null $needle
     * @param string|array|null $replacement
     * @param string $haystack
     * @return string
     */
    public static function replace($needle, $replacement, $haystack)
    {
        return str_replace($needle, $replacement, $haystack);
    }

    /**
     * Search entery's in string $where by string $what
     * @param string $what
     * @param $where
     * @return bool
     */
    public static function contains($what, $where)
    {
        return mb_strpos($where, $what, 0, 'UTF-8') !== false;
    }

    /**
     * Pseudo random [A-Za-z0-9] string with defined $length
     * @param int $length
     * @return string
     */
    public static function randomLatinNumeric($length)
    {
        $ret = 97;
        $out = null;
        for ($i = 0; $i < $length; $i++) {
            $offset = rand(0, 15);
            $char = chr($ret + $offset);
            $posibility = rand(0, 2);
            if ($posibility == 0) {
                // 33% - add random numeric
                $out .= rand(0, 9);
            } elseif ($posibility == 1) {
                // 33% - make upper offset+ret
                $out .= strtoupper($char);
            } else {
                $out .= $char;
            }
        }
        return $out;
    }

    /**
     * Pseudo random [A-Za-z] string with defined $length
     * @param int $length
     * @return string
     */
    public static function randomLatin($length)
    {
        $ret = 97;
        $out = null;
        for ($i = 0; $i < $length; $i++) {
            $offset = rand(0, 15);
            $char = chr($ret + $offset);
            $posibility = rand(0, 1);
            if ($posibility == 1) {
                // 50% - make upper offset+ret
                $out .= strtoupper($char);
            } else {
                $out .= $char;
            }
        }
        return $out;
    }

    /**
     * Check is $string look's like email
     * @param string $string
     * @return bool
     */
    public static function isEmail($string)
    {
        return filter_var($string, FILTER_VALIDATE_EMAIL) !== false;
    }


}